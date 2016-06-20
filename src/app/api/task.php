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
        ROOT => 'api_task_get_all',
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
    'api_task_get_all' => get_role_key(PERFORMER) + get_role_key(CUSTOMER) + get_role_key(SYSTEM),
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
function _validate_amount($amount, &$validation_context)
{
    if (is_null($amount)) {
        _add_validation_error($validation_context, AMOUNT, 'not_provided');
        return false;
    }
    $amount = filter_var($amount, FILTER_VALIDATE_INT);
    if (!$amount) {
        _add_validation_error($validation_context, AMOUNT, 'is_invalid');
        return false;
    }
    if ($amount < get_config_min_amount()) {
        _add_validation_error($validation_context, AMOUNT, 'too_small');
        return false;
    }
    if ($amount > get_config_max_amount()) {
        _add_validation_error($validation_context, AMOUNT, 'too_large');
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
function _validate_id(&$id, $error_key = 'id', &$validation_context)
{
    if (is_null($id)) {
        _add_validation_error($validation_context, $error_key, 'not_provided');
        return false;
    }
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if (!$id) {
        _add_validation_error($validation_context, $error_key, 'is_invalid');
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
function _validate_description($description, &$validation_context)
{
    if (is_null($description) || strlen($description) < 1 || ctype_space($description)) {
        _add_validation_error($validation_context, DESCRIPTION, 'not_provided');
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
function _validate_task_create_input($last_task_id, $amount, $description, $csrf)
{
    $validation_context = initialize_validation_context();
    _validate_task_create_csrf($csrf, get_authorized_user()[ID], $last_task_id, $validation_context);
    _validate_amount($amount, $validation_context);
    _validate_description($description, $validation_context);
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
function _validate_task_fix_input($task_id, $customer_id, $csrf)
{
    $validation_context = initialize_validation_context();
    _validate_id($task_id, ID, $validation_context);
    _validate_id($customer_id, CUSTOMER_ID, $validation_context);
    _validate_customer_task_csrf($csrf, $customer_id, $task_id, $validation_context);
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
function _validate_task_perform_input($task_id, $performer_id, $csrf)
{
    $validation_context = initialize_validation_context();
    _validate_id($task_id, ID, $validation_context);
    _validate_id($task_id, PERFORMER_ID, $validation_context);
    _validate_performer_task_csrf($csrf, $performer_id, $task_id, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        render_bad_request_json([JSON_ERROR => get_all_validation_errors($validation_context)]);
        return false;
    }
    return true;
}

function _try_fix_unprocessed_transaction($user_id, $is_customer = true)
{
    $id = dal_payment_get_last_user_tx_id($user_id);
    if (is_null($id)) {
        return null;
    } elseif ($id === false) {
        return false;
    } else {
        $transactions = dal_payment_fetch_transactions_after($id, $user_id, $is_customer);
        if (is_null($transactions) || $transactions === false) {
            return false;
        } else {
            return _payment_transaction_set_processed($id);
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
function api_task_get_all()
{
    $user = get_authorized_user();
    $user_id = null;
    $select_user_type = null;
    $latest_task_id_query = "";
    if (array_key_exists('HTTP_X_FETCH_NEW', $_SERVER)) {
        $task_ids = json_decode($_SERVER['HTTP_X_FETCH_NEW'], true);
        foreach ($task_ids as $task_id) {
            if (!filter_var($task_id, FILTER_VALIDATE_INT)) {
                render_bad_request_json([JSON_ERROR => [UNSPECIFIED => "invalid"]]);
                return;
            }
        }
        if (!is_null($task_ids) && count($task_ids) > 0) {
            $latest_task_id_query = "AND id in (" . join(",", $task_ids) . ")";
        }
    }
    $last_id = parse_integer_param('last_id');
    $limit = parse_integer_param('limit');
    $limit = $limit < get_config_max_task_selection_limit() ? $limit : get_config_max_task_selection_limit();
    $balance_locked_clause = "TRUE";
    if (is_customer($user[ROLE])) {
        $user_id = $user[ID];
        $select_user_type = CUSTOMER_ID;
    } else if (is_performer($user[ROLE])) {
        $user_id = $user[ID];
        $select_user_type = "(performer_id is null) or not paid and performer_id";
        $balance_locked_clause = BALANCE_LOCKED;
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
    $tasks = dal_task_fetch_tasks_complex_query_limit("_render_task", $user_id, $balance_locked_clause, $select_user_type, $limit, $latest_task_id_query, $last_id);
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
    if (!_validate_task_perform_input($task_id, $performer_id, $csrf)) {
        return;
    }
    dal_payment_fix_last_perform_transaction($performer_id);
    $task = dal_task_fetch($task_id);
    if (!$task) {
        render_forbidden();
        return;
    }
    if ($task && $task[BALANCE_LOCKED]) {
        if (is_null($task[PERFORMER_ID])) {
            $updated = dal_task_update_set_performer_id($task_id, $performer_id);
            if (!$updated) {
                render_conflict([JSON_ERROR => [POPUP => "task_already_performed"]]);
            } else {
                $task[PERFORMER_ID] = $performer_id;
                if (payment_process_complex($task)) {
                    on_payment_success($task);
                } else {
                    on_payment_failure();
                }
            }
        } elseif ($task[PERFORMER_ID] == $performer_id) {
            $tx = dal_payment_get_transaction_by_participants($task[ID], $task[PERFORMER_ID], 'p');
            if (!$tx) {
                if (payment_process_complex($task)) {
                    on_payment_success($task);
                } else {
                    on_payment_failure();
                }
            } else if (!$tx[PROCESSED]) {
                $processed = dal_payment_process_pay_transaction($tx[ID], $task[CUSTOMER_ID], $performer_id, $task[PRICE], $task[COMMISSION]);
                if ($processed) {
                    $paid_success = dal_task_update_set_paid($task[ID]);
                    if ($paid_success) {
                        $task[PAID] = true;
                        on_payment_success($task);
                    } else
                        on_payment_failure();
                } else {
                    on_payment_failure();
                }
            } else {
                $paid_success = dal_task_update_set_paid($task[ID]);
                if ($paid_success) {
                    $task[PAID] = true;
                    on_payment_success($task);
                } else {
                    on_payment_failure();
                }
            }
        } else {
            render_conflict([JSON_ERROR => [POPUP => "task_already_performed"]]);
        }
    } else {
        render_forbidden();
    }
    return;
}

function set_task_paid(&$task)
{
    $task[PAID] = dal_task_update_set_paid($task[ID]);
}

function on_payment_success($task)
{
    send_generic_event(null, get_role_key(PERFORMER), $task[ID], 'p');
    send_generic_event($task[CUSTOMER_ID], get_role_key(CUSTOMER), $task[ID], 'p');
    render_ok_json($task);
    return;
}

function on_payment_failure()
{
    render_internal_server_error(get_dal_errors());
    return;
}

/**
 * @param $task
 * @return bool
 */
function payment_process_complex($task)
{
    $tx_id = dal_payment_init_pay_transaction($task[ID], $task[PERFORMER_ID], $task[PRICE]);
    $processed = dal_payment_process_pay_transaction($tx_id, $task[CUSTOMER_ID], $task[PERFORMER_ID], $task[PRICE], $task[COMMISSION]);
    if ($processed) {
        $paid_success = dal_task_update_set_paid($task[ID]);
        if ($paid_success)
            return true;
        else
            return false;
    } else {
        return false;
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
    if (!_validate_task_create_input($last_task_id, $amount, $description, $csrf)) {
        return;
    }
    if (!is_null($last_task) && !$last_task[BALANCE_LOCKED]) {
        $transaction_fix_result = _try_fix_unprocessed_transaction($customer_id, true);
        if (is_null($transaction_fix_result) || $transaction_fix_result === false) {
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
    if (!dal_payment_check_able_to_process($customer_id, $amount)) {
        render_conflict([
            JSON_ERROR => ["amount" => "not_enough"]
        ]);
        return;
    }
    $task_id = dal_task_create($customer_id, $amount, $description);
    $lock_tx_id = dal_payment_init_lock_transaction($customer_id, $task_id, $amount);
    $success = dal_payment_process_lock_transaction($lock_tx_id, $customer_id);
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
    send_generic_event(null, get_role_key(PERFORMER), $task_id, 'c');
    send_generic_event($user[ID], get_role_key(CUSTOMER), $task_id, 'c');
    if (!$task) {
        error_log("Couldn't fetch task");
        render_bad_request_json([JSON_ERROR => get_dal_errors()]);
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
    if (!_validate_task_fix_input($task_id, $customer_id, $csrf)) {
        return;
    }
    $amount = dal_task_fetch_non_locked_price($task_id, $customer_id);
    if (is_null($amount) || !$amount) {
        render_bad_request_json([JSON_ERROR => [UNSPECIFIED => "task_unable_to_process"]]);
        return;
    }
    if (!dal_payment_check_able_to_process($customer_id, $amount)) {
        render_conflict([JSON_ERROR => [POPUP => "task_not_enough_money"]]);
        return;
    }
    $tx_lock_processed = dal_payment_retry_lock_transaction($task_id, $customer_id, $amount);
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
    if (!_validate_customer_task_csrf($csrf, $customer_id, $task_id, $validation_context)) {
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
    $GLOBALS['current_task'] = $task;
    require 'view/templates/task.html.php';
}

route_request($routes, $authorization);
