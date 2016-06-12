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
 * @param $error_key string
 * @param $validation_context array
 * @return bool true if validation succeeds and false otherwise
 */
function __validate_id(&$id, $error_key = 'id', &$validation_context)
{
    if (is_null($id)) {
        add_validation_error($validation_context, $error_key, 'not_provided');
        return false;
    }
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if (!$id) {
        add_validation_error($validation_context, $error_key, 'is_invalid');
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
        add_validation_error($validation_context, DESCRIPTION, 'not_provided');
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
    __validate_id($task_id, ID, $validation_context);
    __validate_id($customer_id, CUSTOMER_ID, $validation_context);
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
    __validate_id($task_id, PERFORMER_ID, $validation_context);
    __validate_performer_task_csrf($csrf, $performer_id, $task_id, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        render_bad_request_json([JSON_ERROR => get_all_validation_errors($validation_context)]);
        return false;
    }
    return true;
}

function __try_fix_unprocessed_transaction($user_id, $is_customer = true)
{
    $id = payment_get_last_user_tx_id($user_id);
    if (is_null($id)) {
        return null;
    } elseif ($id == false) {
        return false;
    } else {
        $transactions = payment_fetch_transactions_after($id, $user_id, $is_customer);
        if (is_null($transactions) || $transactions == false) {
            return false;
        } else {
            return __payment_transaction_set_processed($id);
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
    $balance_locked_clause = "TRUE";
    if (is_customer($user[ROLE])) {
        $user_id = $user[ID];
        $select_user_type = CUSTOMER_ID;
    } else {
        $select_user_type = PERFORMER_ID;
        $balance_locked_clause = BALANCE_LOCKED;
    }
    if (is_customer($user[ROLE]) && is_null($last_id)) {
        $task = dal_task_fetch_last($user[ID]);
        $last_task_id = $task ? $task[ID] : -1;
        $create_csrf = get_customer_task_create_csrf($user[ID], $last_task_id);
        echo "<!--json-$create_csrf-json-->";
    }
    $tasks = dal_task_fetch_tasks_less_than_last_id_limit("_render_task", $user_id, $balance_locked_clause, $select_user_type, $limit, $last_id);
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
    if (!__validate_task_perform_input($task_id, $performer_id, $csrf)) {
        return;
    }
    $task = dal_task_fetch($task_id);
    if ($task[BALANCE_LOCKED]) {
        if (is_null($task[PERFORMER_ID])) {
            $updated = dal_task_update_set_performer_id($task_id, $performer_id);
            if (!$updated) {
                render_conflict([JSON_ERROR => [POPUP => "task_already_performed"]]);
                return;
            } else {
                payment_init_pay_transaction($task[ID], $performer_id, $task[AMOUNT], $task[COMMISSION]);
            }
        } else {
            render_conflict([JSON_ERROR => [POPUP => "task_already_performed"]]);
            return;
        }
    } else {
        render_forbidden();
        return;
    }
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
    if (!is_null($last_task) && !$last_task[BALANCE_LOCKED]) {
        $transaction_fix_result = __try_fix_unprocessed_transaction($customer_id, true);
        if (is_null($transaction_fix_result) || $transaction_fix_result == false) {
            render_conflict([
                JSON_ERROR => ["task-unpaid" => true]
            ]);
            return;
        } else {
            if (!dal_task_update_set_balance_locked($last_task_id)) {
                render_conflict([
                    JSON_ERROR => ["task-unpaid" => true]
                ]);
                return;
            }
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
    $success = payment_process_lock_transaction($lock_tx_id, $customer_id);
    if (is_null($success)) {
        render_conflict([
            "error" => ["amount" => "not_enough"]
        ]);
        return;
    } elseif (!$success) {
        render_internal_server_error();
        return;
    }
    $updated = dal_task_update_set_balance_locked($task_id);
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
    if (!__validate_task_fix_input($task_id, $customer_id, $csrf)) {
        return;
    }
    $amount = dal_task_fetch_non_locked_price($task_id, $customer_id);
    if (is_null($amount) || !$amount) {
        render_bad_request_json([JSON_ERROR => [UNSPECIFIED => "task_unable_to_process"]]);
        return;
    }
    if (!payment_check_able_to_process($customer_id, $amount)) {
        render_conflict([JSON_ERROR => [POPUP => "task_not_enough_money"]]);
        return;
    }
    $tx_lock_processed = payment_retry_lock_transaction($task_id, $customer_id, $amount);
    if ($tx_lock_processed === true) {
        $updated = dal_task_update_set_balance_locked($task_id);
        if (!$updated) {
            $updated = dal_task_update_set_balance_locked($task_id);
        }
        if (!$updated) {
            render_internal_server_error();
        } else {
            _render_task(dal_task_fetch($task_id));
        }
    } elseif (is_null($tx_lock_processed)) {
        render_conflict([JSON_ERROR => [POPUP => "task_not_enough_money"]]);
    } else {
        render_internal_server_error();
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
    if ($task[BALANCE_LOCKED]) {
        render_conflict([JSON_ERROR => [POPUP => 'task_already_paid']]);
        return;
    }
    $task_deleted = dal_task_delete($task_id);
    if ($task_deleted) {
        render_ok_json("");
    } elseif (is_null($task_deleted)) {
        render_conflict([JSON_ERROR => [POPUP => 'task_already_paid']]);
    } else {
        render_internal_server_error();
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