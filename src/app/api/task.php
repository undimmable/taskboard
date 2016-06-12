<?php
/**
 * Task manipulation functions
 *
 * PHP version 5
 *
 * @category  APIFunctions
 * @package   Api
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */
/**
 * Require bootstrap, task and payment
 */
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

/**
 * Validate task amount
 *
 * @param $amount integer
 * @param $validation_context array
 * @return bool true if validation succeeds and false otherwise
 */
function __validate_amount($amount, &$validation_context)
{
    if (is_null($amount)) {
        add_validation_error($validation_context, AMOUNT, 'not_provided');
        return false;
    }
    $amount = filter_var($amount, FILTER_VALIDATE_INT);
    if (!$amount) {
        add_validation_error($validation_context, AMOUNT, 'is_invalid');
        return false;
    }
    if ($amount < get_config_min_amount()) {
        add_validation_error($validation_context, AMOUNT, 'too_small');
        return false;
    }
    if ($amount > get_config_max_amount()) {
        add_validation_error($validation_context, AMOUNT, 'too_large');
        return false;
    }
    return true;
}

/**
 * Validate task id
 *
 * @param $id integer
 * @param $validation_context array
 * @return bool true if validation succeeds and false otherwise
 */
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

/**
 * Validate task description
 *
 * @param $description string
 * @param $validation_context array
 * @return bool true if validation succeeds and false otherwise
 */
function __validate_description($description, &$validation_context)
{
    if (is_null($description) || strlen($description) < 1 || ctype_space($description)) {
        add_validation_error($validation_context, DESCRIPTION, 'Description not provided');
        return false;
    }
    return true;
}

/**
 * Validate task creation request provided correct values
 *
 * @param $last_task_id integer
 * @param $amount integer
 * @param $description string
 * @param $csrf string
 * @return bool true if validation succeeds and false otherwise
 */
function __validate_task_create_input($last_task_id, $amount, $description, $csrf)
{
    $validation_context = initialize_validation_context();
    __validate_task_create_csrf($csrf, get_authorized_user()[ID], $last_task_id, $validation_context);
    __validate_amount($amount, $validation_context);
    __validate_description($description, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        render_bad_request_json([JSON_ERROR => get_all_validation_errors($validation_context)]);
        return false;
    }
    return true;
}

/**
 * Validate task fix request provided correct values
 *
 * @param $task_id integer
 * @param $customer_id int
 * @param $csrf string
 * @return bool true if validation succeeds and false otherwise
 */
function __validate_task_fix_input($task_id, $customer_id, $csrf)
{
    $validation_context = initialize_validation_context();
    __validate_id($task_id, $validation_context);
    __validate_customer_task_csrf($csrf, $customer_id, $task_id, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        render_bad_request_json([JSON_ERROR => get_all_validation_errors($validation_context)]);
        return false;
    }
    return true;
}

/**
 * Validate perform task provided correct values
 *
 * @param $task_id integer
 * @param $performer_id integer
 * @param $csrf string
 * @return bool true if validation succeeds and false otherwise
 */
function __validate_task_perform_input($task_id, $performer_id, $csrf)
{
    $validation_context = initialize_validation_context();
    __validate_id($task_id, $validation_context);
    __validate_performer_task_csrf($csrf, $performer_id, $task_id, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        render_bad_request_json([JSON_ERROR => get_all_validation_errors($validation_context)]);
        return false;
    }
    return true;
}

function __try_fix_unprocessed_transaction($user_id)
{
    $id = payment_get_last_user_tx_id($user_id);
    if (is_null($id)) {
        return null;
    } elseif (!$id) {
        return false;
    } else {
        $transactions = payment_fetch_transactions_after($id);
        if (!$transactions) {
            return false;
        } else {
            return true;
        }
    }
}

/**
 * Api get task as json by id
 *
 * @param $task_id integer
 */
function api_task_get_by_id($task_id)
{
    $validation_context = initialize_validation_context();
    $task_id = filter_var($task_id, FILTER_VALIDATE_INT);
    $task = dal_task_fetch($task_id);
    if ($task === null) {
        render_not_found();
        die;
    } elseif (!$task) {
        render_bad_request_json([JSON_ERROR => get_all_validation_errors($validation_context)]);
        die;
    } else {
        render_ok_json($task);
    }
}

/**
 * Api get last n tasks for user
 */
function api_task_get_last_n()
{
    $user = get_authorized_user();
    $user_id = null;
    $select_user_type = null;
    $last_id = parse_integer_param('last_id');
    $limit = parse_integer_param('limit');
    $limit = $limit < get_config_max_task_selection_limit() ? $limit : get_config_max_task_selection_limit();
    $paid_clause = "TRUE";
    if (is_customer($user[ROLE])) {
        $user_id = $user[ID];
        $select_user_type = 'customer_id';
    } else {
        $select_user_type = 'performer_id';
        $paid_clause = "paid";
    }
    if (is_customer($user[ROLE]) && is_null($last_id)) {
        $task = dal_task_fetch_last($user[ID]);
        $last_task_id = $task ? $task[ID] : -1;
        $create_csrf = get_customer_task_create_csrf($user[ID], $last_task_id);
        echo "<!--json-$create_csrf-json-->";
    }
    $tasks = dal_task_fetch_tasks_less_than_last_id_limit("_render_task", $user_id, $paid_clause, $select_user_type, $limit, $last_id);
    if (is_null($tasks)) {
        render_ok();
    } else if ($tasks === false) {
        render_internal_server_error();
    }
}

/**
 * Api perform task
 *
 * @param $task_id integer
 */
function api_task_perform($task_id)
{
    $user = get_authorized_user();
    $performer_id = $user[ID];
    $csrf = parse_csrf_token_header();
    __validate_task_perform_input($task_id, $performer_id, $csrf);

}

/**
 * Api create task
 */
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
    $last_task = dal_task_fetch_last($customer_id);
    $last_task_id = $last_task ? $last_task[ID] : -1;
    if (!__validate_task_create_input($last_task_id, $amount, $description, $csrf)) {
        return;
    }
    if (!$last_task[PAID]) {
        if (!__try_fix_unprocessed_transaction($customer_id)) {
            render_conflict([
                JSON_ERROR => ["task-unpaid" => true]
            ]);
        }
    }
    if (!payment_check_able_to_process($customer_id, $amount)) {
        render_conflict([
            JSON_ERROR => ["amount" => "not_enough"]
        ]);
        return;
    }
    $task_id = dal_task_create($customer_id, $amount, $description);
    $lock_tx_id = payment_init_lock_transaction($customer_id, $task_id, $amount);
    $success = payment_process_transaction($lock_tx_id, $customer_id);
    if (is_null($success)) {
        render_conflict([
            "error" => ["amount" => "not_enough"]
        ]);
        return;
    } elseif (!$success) {
        render_internal_server_error();
        return;
    }
    $updated = dal_task_update_set_paid($task_id);
    if (is_null($updated)) {
        error_log("Trying set lock_tx_id when it's already set");
    } elseif (!$updated) {
        error_log("Setting lock_tx_id failed");
    }
    $task = dal_task_fetch($task_id);
    if (!$task) {
        error_log("Couldn't fetch task");
        render_bad_request_json([JSON_ERROR => get_db_errors()]);
        return;
    } else {
        send_index_event($task[ID], TASK_DESCRIPTION_IDX, $task[DESCRIPTION]);
        $create_csrf = get_customer_task_create_csrf($user[ID], $task[ID]);
        echo "<!--json-$create_csrf-json-->";
        _render_task($task);
        return;
    }
}

/**
 * Api fix task
 *
 * @param $task_id integer
 */
function api_task_fix($task_id)
{
    $user = get_authorized_user();
    $customer_id = $user[ID];
    $csrf = parse_csrf_token_header();
    __validate_task_fix_input($task_id, $customer_id, $csrf);
    $amount = dal_task_fetch_unpaid_price($task_id, $customer_id);
    if (is_null($amount) || !$amount) {
        render_bad_request_json([JSON_ERROR => [TASK_DB => "unable_to_process"]]);
        return;
    }
    if (!payment_check_able_to_process($customer_id, $amount)) {
        render_conflict([JSON_ERROR => [AMOUNT => "not_enough"]]);
        return;
    }

    $lock_tx_id = payment_get_unprocessed_transaction($customer_id, $task_id, 'l');
    if (is_null($lock_tx_id)) {

    }
    $success = payment_lock_balance($customer_id, $lock_tx_id, $amount);
    if (is_null($success)) {
        render_conflict([JSON_ERROR => [AMOUNT => "not_enough"]]);
        return;
    } elseif (!$success) {
        render_internal_server_error();
        return;
    }
    $updated = dal_task_update_set_paid($task_id);
    if (is_null($updated)) {
        error_log("Trying set lock_tx_id when it's already set");
    } else {
        error_log("Setting lock_tx_id failed");
    }
    $task = dal_task_fetch($task_id);
    if (!$task) {
        error_log("Couldn't fetch task");
        render_bad_request_json([JSON_ERROR => get_db_errors()]);
        return;
    } else {
        send_index_event($task[ID], TASK_DESCRIPTION_IDX, $task[DESCRIPTION]);
        $create_csrf = get_customer_task_create_csrf($customer_id, $task[ID]);
        echo "<!--json-$create_csrf-json-->";
        _render_task($task);
        return;
    }
}

/**
 * Api delete task
 *
 * @param $task_id integer
 */
function api_task_delete_by_id($task_id)
{
    $user = get_authorized_user();
    $customer_id = $user[ID];
    $csrf = parse_csrf_token_header();
    $validation_context = initialize_validation_context();
    if (!__validate_customer_task_csrf($csrf, $customer_id, $task_id, $validation_context)) {
        if (validation_context_has_errors($validation_context)) {
            render_bad_request_json([JSON_ERROR => get_all_validation_errors($validation_context)]);
            return;
        }
    }
    $task = dal_task_fetch($task_id);
    if (!$task || $task[CUSTOMER_ID] != $customer_id) {
        render_forbidden();
        return;
    }
    if ($task[PAID]) {
        render_conflict([JSON_ERROR => ['task' => 'already_paid']]);
        return;
    }
    $task_deleted = dal_task_delete($task_id);
    if ($task_deleted) {
        render_ok_json("");
    } else {
        render_conflict([JSON_ERROR => [UNSPECIFIED => 'task_already_paid']]);
    }
    return;
}

/**
 * Render task from template
 *
 * @param $task array
 */
function _render_task($task)
{
    global $current_task;
    $current_task = $task;
    require 'view/templates/task.html.php';
}

route_request($routes, $authorization);