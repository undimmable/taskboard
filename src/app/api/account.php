<?php
/**
 * Accounting functions
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
 * Require bootstrap
 */
require_once "../bootstrap.php";
require_once "dal/payment.php";

$routes = [
    'GET' => [
        ROOT => 'api_get_balance'
    ],
    'POST' => [
        ROOT => 'api_refill_balance'
    ],
    'PUT' => [],
    'DELETE' => []
];

$authorization = [
    'api_get_balance' => get_role_key(SYSTEM) + get_role_key(CUSTOMER) + get_role_key(PERFORMER),
    'api_refill_balance' => get_role_key(CUSTOMER)
];

/**
 * Validate amount
 *
 * @param $amount integer
 * @param $validation_context array
 * @return bool true if validation succeeds and false otherwise
 */
function _validate_amount($amount, &$validation_context)
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
 * Validate account request provided correct values
 *
 * @param $amount integer
 * @param $user_id integer
 * @param $csrf string
 * @return bool true if validation succeeds and false otherwise
 */

function _validate_account_input($amount, $user_id, $csrf)
{
    $validation_context = initialize_validation_context();
    is_account_csrf_token_valid($csrf, $user_id, $validation_context);
    _validate_amount($amount, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        render_bad_request_json([JSON_ERROR => get_all_validation_errors($validation_context)]);
        return false;
    }
    return true;
}

/**
 * Api get user balance
 */
function api_get_balance()
{
    $user_id = get_authorized_user()[ID];
    $balance = payment_fetch_balance($user_id);
    if (!$balance) {
        render_internal_server_error([JSON_ERROR => get_db_errors()]);
        return;
    }
    render_ok_json(["balance" => $balance]);
}

/**
 * Api refill user balance
 */
function api_refill_balance()
{
    $user = get_authorized_user();
    if (!is_customer($user[ROLE])) {
        render_forbidden();
        return;
    }
    if (!is_request_json()) {
        render_unsupported_media_type();
        return;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $customer_id = $user[ID];
    $amount = $data[AMOUNT];
    $csrf = parse_csrf_token_header();
    if (!_validate_account_input($amount, $customer_id, $csrf)) {
        return;
    }
    $updated = payment_refill_balance($customer_id, $amount);
    if (!$updated) {
        render_internal_server_error([JSON_ERROR => get_db_errors()]);
        return;
    } else {
        api_get_balance();
    }
}

route_request($routes, $authorization);