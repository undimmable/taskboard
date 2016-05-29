<?php

require_once "../bootstrap.php";
require_once "../dal/task.php";

$routes = [
    POST => [
        ROOT => 'api_task_create'
    ],
    GET => [
        ROOT => 'api_task_get_last_n',
        '/\d+/' => 'api_task_get_by_id'
    ],
    PUT => [
        '/\d+/' => 'api_task_update_by_id'
    ],
    DELETE => [
        '/\d+/' => 'api_task_delete_by_id'
    ]
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

function __validate_description($description, &$validation_context)
{
    if (is_null($description) || strlen($description) < 1) {
        add_validation_error($validation_context, DESCRIPTION, 'Description not provided');
        return false;
    }
    return true;
}

function __validate_task_input($amount, $description, $csrf)
{
    $validation_context = initialize_validation_context();
    is_csrf_token_valid("task", $csrf, $validation_context);
    __validate_amount($amount, $validation_context);
    __validate_description($description, $validation_context);
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
    if (!is_authorized())
        render_not_authorized_json();
    $user = get_authorized_user();
    $user_id = null;
    $select_user_type = null;
    $last_id = parse_integer_param('last_id');
    $limit = parse_integer_param('limit');
    $limit = $limit < get_config_max_task_selection_limit() ? $limit : get_config_max_task_selection_limit();
    if (is_customer($user)) {
        $user_id = $user[ID];
        $select_user_type = 'customer_id';
    } else {
        $select_user_type = 'performer_id';
    }
    $tasks = dal_task_fetch_tasks_less_than_last_id_limit("api_render_task", $user_id, $select_user_type, $limit, $last_id);
    if (is_null($tasks)) {
        render_no_content();
    } else if ($tasks === false) {
        render_bad_request_json($tasks);
    }
}

function api_task_update_by_id($id, $task)
{
    echo $task;
    echo $id;
}

function api_task_create()
{
    if (!is_authorized()) {
        render_not_authorized_json();
        return;
    }
    $user = get_authorized_user();
    if (!is_customer(get_authorized_user()[ROLE])) {
        render_forbidden();
        return;
    }
    if (is_request_www_form()) {
        $data = $_POST;
    } elseif (is_request_json()) {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        render_unsupported_media_type();
        return;
    }
    $customer_id = $user[ID];
    $amount = $data[AMOUNT];
    $description = $data[DESCRIPTION];
    $csrf = $data['csrf_token'];
    if (!__validate_task_input($amount, $description, $csrf)) {
        return;
    }
    if (!payment_check_able_to_process($customer_id, $amount)) {
        render_conflict([
            "error" => "Not enough money"
        ]);
        return;
    }
    $task_id = dal_task_create($customer_id, $amount, $description);
    $lock_tx_id = payment_create_transaction($customer_id, $customer_id, $amount, 'l');
    $success = payment_lock_balance($customer_id, $lock_tx_id, $amount);
    if (is_null($success)) {
        render_conflict([
            "error" => "Not enough money"
        ]);
        return;
    } elseif (!$success) {
        render_internal_server_error([
            "error" => "Something went wrong"
        ]);
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
        api_render_task($task);
        return;
    }
}

function api_render_task(/** @noinspection PhpUnusedParameterInspection */
    $task)
{
    require '../view/templates/task.html.php';
}

route_request($routes);