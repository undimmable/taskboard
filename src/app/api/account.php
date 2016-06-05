<?php
/**
 * @author dimyriy
 * @version 1.0
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

function __validate_account_input($amount, $csrf)
{
    $validation_context = initialize_validation_context();
    is_csrf_token_valid("account", $csrf, $validation_context);
    __validate_amount($amount, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        render_bad_request_json(['error' => get_all_validation_errors($validation_context)]);
        return false;
    }
    return true;
}

function api_get_balance()
{
    $user_id = get_authorized_user()[ID];
    $balance = payment_fetch_balance($user_id);
    if (!$balance) {
        render_internal_server_error(["error" => get_db_errors()]);
        return;
    }
    render_ok_json(["balance" => $balance]);
}

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
    if (!__validate_amount($amount, $csrf)) {
        return;
    }
    $updated = payment_refill_balance($customer_id, $amount);
    if (!$updated) {
        render_internal_server_error(["error" => get_db_errors()]);
        return;
    } else {
        api_get_balance();
    }
}

route_request($routes, $authorization);