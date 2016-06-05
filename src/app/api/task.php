<?php

require_once "../bootstrap.php";
require_once "dal/task.php";
require_once "dal/payment.php";

$routes = [
    POST => [
        ROOT => 'api_task_create',
        '/\d+/' => 'api_task_fix'
    ],
    GET => [
        ROOT => 'api_task_get_last_n',
        '/\d+/' => 'api_task_get_by_id'
    ],
    PUT => [
        '/\d+/' => 'api_task_perform'
    ],
    DELETE => [
        '/\d+/' => 'api_task_delete_by_id'
    ]
];

$authorization = [
    'api_task_create' => get_role_key(CUSTOMER),
    'api_task_fix' => get_role_key(CUSTOMER),
    'api_task_get_last_n' => get_role_key(PERFORMER) + get_role_key(CUSTOMER) + get_role_key(SYSTEM),
    'api_task_get_by_id' => get_role_key(CUSTOMER),
    'api_task_perform' => get_role_key(PERFORMER),
    'api_task_delete_by_id' => get_role_key(CUSTOMER)
];


function __validate_amount($amount, &$validation_context)
{
    if (is_null($amount)) {
        add_validation_error($validation_context, AMOUNT, 'Price not provided');
        return false;
    }
    $amount = filter_var($amount, FILTER_VALIDATE_INT);
    if (!$amount) {
        add_validation_error($validation_context, AMOUNT, 'Is not a valid number');
        return false;
    }
    if ($amount < get_config_min_amount()) {
        add_validation_error($validation_context, AMOUNT, 'Price cannot be less than ' . get_config_min_amount());
        return false;
    }
    if ($amount > get_config_max_amount()) {
        add_validation_error($validation_context, AMOUNT, 'Price cannot be larger than ' . get_config_max_amount());
        return false;
    }
    return true;
}


function __validate_id(&$id, &$validation_context)
{
    if (is_null($id)) {
        add_validation_error($validation_context, ID, 'ID not provided');
        return false;
    }
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if (!$id) {
        add_validation_error($validation_context, ID, 'Is not a valid number');
        return false;
    }
    return true;
}

function __validate_description($description, &$validation_context)
{
    if (is_null($description) || strlen($description) < 1) {
        add_validation_error($validation_context, DESCRIPTION, 'Description not provided');
        return false;
    }
    return true;
}

function __validate_task_input($last_task_id, $amount, $description, $csrf)
{
    $validation_context = initialize_validation_context();
    __validate_task_create_csrf($csrf, get_authorized_user()[ID], $last_task_id, $validation_context);
    __validate_amount($amount, $validation_context);
    __validate_description($description, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        render_bad_request_json(['error' => get_all_validation_errors($validation_context)]);
        return false;
    }
    return true;
}

function __validate_task_fix_input($id, $csrf)
{
    $validation_context = initialize_validation_context();
    __validate_id($id, $validation_context);
    __validate_customer_task_csrf($csrf, get_authorized_user()[ID], $id, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        render_bad_request_json(['error' => get_all_validation_errors($validation_context)]);
        return false;
    }
    return true;
}

function api_task_get_by_id($task_id)
{
    $validation_context = initialize_validation_context();
    $task_id = filter_var($task_id, FILTER_VALIDATE_INT);
    $task = dal_task_fetch($task_id);
    if ($task === null) {
        render_not_found();
        die;
    } elseif (!$task) {
        render_bad_request_json(['error' => get_all_validation_errors($validation_context)]);
        die;
    } else {
        render_ok_json($task);
    }
}

function api_task_get_last_n()
{
    $user = get_authorized_user();
    $user_id = null;
    $select_user_type = null;
    $last_id = parse_integer_param('last_id');
    $limit = parse_integer_param('limit');
    $limit = $limit < get_config_max_task_selection_limit() ? $limit : get_config_max_task_selection_limit();
    $lock_tx_id_clause = "TRUE";
    if (is_customer($user[ROLE])) {
        $user_id = $user[ID];
        $select_user_type = 'customer_id';
    } else {
        $select_user_type = 'performer_id';
        $lock_tx_id_clause = "lock_tx_id != -1";
    }
    if (is_customer($user[ROLE]) && is_null($last_id)) {
        $create_csrf = get_customer_task_create_csrf($user[ID], dal_task_get_last_id($user[ID]));
        echo "<!--json-$create_csrf-json-->";
    }
    $tasks = dal_task_fetch_tasks_less_than_last_id_limit("__render_task", $user_id, $lock_tx_id_clause, $select_user_type, $limit, $last_id);
    if (is_null($tasks)) {
        render_ok();
    } else if ($tasks === false) {
        render_internal_server_error();
    }
}

function api_task_perform()
{
//    $user = get_authorized_user();
}

function api_task_create()
{
    if (!is_request_json()) {
        render_unsupported_media_type();
        return;
    }
    $user = get_authorized_user();
    $data = json_decode(file_get_contents('php://input'), true);
    $customer_id = $user[ID];
    $amount = $data[AMOUNT];
    $description = $data[DESCRIPTION];
    $csrf = parse_csrf_token_header();
    $last_task_id = dal_task_get_last_id($customer_id);
    if (!__validate_task_input($last_task_id, $amount, $description, $csrf)) {
        return;
    }
    if (!payment_check_able_to_process($customer_id, $amount)) {
        render_conflict([
            "error" => ["amount" => "Not enough money"]
        ]);
        return;
    }
    $task_id = dal_task_create($customer_id, $amount, $description);
    $lock_tx_id = payment_create_transaction($customer_id, $customer_id, $amount, 'l');
    $success = payment_lock_balance($customer_id, $lock_tx_id, $amount);
    if (is_null($success)) {
        render_conflict([
            "error" => ["amount" => "Not enough money"]
        ]);
        return;
    } elseif (!$success) {
        render_internal_server_error();
        return;
    }
    $updated = dal_task_update_set_lock_tx_id($task_id, $lock_tx_id);
    if (is_null($updated)) {
        error_log("Trying set lock_tx_id when it's already set");
    } elseif (!$updated) {
        error_log("Setting lock_tx_id failed");
    }
    $task = dal_task_fetch($task_id);
    if (!$task) {
        error_log("Couldn't fetch task");
        render_bad_request_json(['error' => get_db_errors()]);
        return;
    } else {
        send_index_event($task[ID], TASK_DESCRIPTION_IDX, $task[DESCRIPTION]);
        $create_csrf = get_customer_task_create_csrf($user[ID], $task);
        echo "<!--json-$create_csrf-json-->";
        __render_task($task);
        return;
    }
}

function api_task_fix($id)
{
    $user = get_authorized_user();
    $data = json_decode(file_get_contents('php://input'), true);
    $customer_id = $user[ID];
    $amount = $data[AMOUNT];
    $description = $data[DESCRIPTION];
    $csrf = parse_csrf_token_header();
    __validate_task_fix_input($id, $csrf);
    if (!payment_check_able_to_process($customer_id, $amount)) {
        render_conflict([
            "error" => ["amount" => "Not enough money"]
        ]);
        return;
    }
    $task_id = dal_task_create($customer_id, $amount, $description);
    $lock_tx_id = payment_create_transaction($customer_id, $customer_id, $amount, 'l');
    $success = payment_lock_balance($customer_id, $lock_tx_id, $amount);
    if (is_null($success)) {
        render_conflict([
            "error" => ["amount" => "Not enough money"]
        ]);
        return;
    } elseif (!$success) {
        render_internal_server_error();
        return;
    }
    $updated = dal_task_update_set_lock_tx_id($task_id, $lock_tx_id);
    if (is_null($updated)) {
        error_log("Trying set lock_tx_id when it's already set");
    } else {
        error_log("Setting lock_tx_id failed");
    }
    $task = dal_task_fetch($task_id);
    if (!$task) {
        error_log("Couldn't fetch task");
        render_bad_request_json(['error' => get_db_errors()]);
        return;
    } else {
        send_index_event($task[ID], TASK_DESCRIPTION_IDX, $task[DESCRIPTION]);
        $create_csrf = get_customer_task_create_csrf($customer_id, $task[ID]);
        echo "<!--json-$create_csrf-json-->";
        __render_task($task);
        return;
    }
}


function api_task_delete_by_id($task_id)
{
    $user = get_authorized_user();
    $customer_id = $user[ID];
    $csrf = parse_csrf_token_header();
    $validation_context = initialize_validation_context();
    if (!__validate_customer_task_csrf($csrf, $customer_id, $task_id, $validation_context)) {
        if (validation_context_has_errors($validation_context)) {
            render_bad_request_json(['error' => get_all_validation_errors($validation_context)]);
            return;
        }
    }
    $task = dal_task_fetch($task_id);
    if (!$task || $task[CUSTOMER_ID] != $customer_id) {
        render_forbidden();
        return;
    }
    $task_deleted = dal_task_delete($task_id);
    if ($task_deleted) {
        payment_unlock_balance($customer_id, $task[AMOUNT]);
    }
    return;
}

function __render_task($task)
{
    global $current_task;
    $current_task = $task;
    require 'view/templates/task.html.php';
}

route_request($routes, $authorization);