<?php
/**
 * Login functions
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
require_once "../bootstrap.php";
require_once "lib/mail.php";
require_once "dal/user.php";
require_once "dal/login.php";
require_once "dal/payment.php";

$routes = [
    'POST' => [
        'login' => 'api_auth_login_action',
        'signup' => 'api_auth_signup_action',
    ],
    'GET' => [
        'verify' => 'api_auth_verify_action',
        'logout' => 'api_auth_logout_action',
        'signup_vk' => 'api_auth_signup_vk_action'
    ],
    'PUT' => [],
    'DELETE' => []
];

$authorization = [
    'api_auth_login_action' => auth_unauthenticated(),
    'api_auth_signup_action' => auth_unauthenticated(),
    'api_auth_verify_action' => auth_unauthenticated(),
    'api_auth_logout_action' => auth_any_authenticated(),
    'api_auth_signup_vk_action' => auth_unauthenticated()
];

function __validate_signup_input($email, $role, $password, $password_repeat, $csrf)
{
    $validation_context = initialize_validation_context();
    is_signup_csrf_token_valid($csrf, $validation_context);
    is_email_valid($email, $validation_context);
    is_role_valid($role, $validation_context);
    is_password_valid($password, $validation_context);
    is_password_repeat_valid($password, $password_repeat, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        render_bad_request_json(['error' => get_all_validation_errors($validation_context)]);
        return false;
    }
    return true;
}

function __validate_login_input($email, $password, $csrf)
{
    $validation_context = initialize_validation_context();
    is_login_csrf_token_valid($csrf, $validation_context);
    is_email_valid($email, $validation_context);
    is_password_valid($password, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        render_bad_request_json(['error' => get_all_validation_errors($validation_context)]);
        return false;
    }
    return true;
}

function api_auth_login_action()
{
    if (!is_request_json()) {
        render_unsupported_media_type();
        return;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data[EMAIL];
    $password = $data[PASSWORD];
    $remember_me = is_checked($data[REMEMBER_ME]);
    $csrf = parse_csrf_token_header();
    if (!__validate_login_input($email, $password, $csrf)) {
        return;
    }
    $ip = parse_ip();
    $client = parse_user_client();
    $interval = get_config_failed_attempt_timeout();
    $failed_attempts = dal_login_being_failed($ip, $client, get_config_failed_attempt_retry(), $interval);
    if ($failed_attempts) {
        render_not_authorized_json(['error' => [EMAIL => "Max attempts number exceeded. Try again in $interval seconds"]]);
        return;
    }
    $user = db_fetch_user_by_email($email);
    if ($user === null || !password_verify($password, $user[HASHED_PASSWORD])) {
        dal_login_log_failed($ip, $client);
        render_not_authorized_json([
            'error' => [EMAIL => 'Wrong username and/or password']
        ]);
        return;
    }
    dal_login_create_or_update($user[ID], $ip, $client);
    $token = create_jwt_token($user[EMAIL], $user[ROLE], $user[ID]);
    set_token_cookie($token, !$remember_me);
    render_ok_json([
        'redirect' => '/'
    ]);
}

function api_auth_signup_action()
{
    if (!is_request_json()) {
        render_unsupported_media_type();
        return;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data[EMAIL];
    $is_customer = $data[IS_CUSTOMER];
    $password = $data[PASSWORD];
    $password_repeat = $data[PASSWORD_REPEAT];
    if (is_checked($is_customer)) {
        $role = get_role_key(CUSTOMER);
    } else {
        $role = get_role_key(PERFORMER);
    }
    $csrf = parse_csrf_token_header();
    if (!__validate_signup_input($email, $role, $password, $password_repeat, $csrf)) {
        return;
    }
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $confirmation_token = create_confirmation_token($email);
    $user = create_user($email, $role, $hashed_password, $confirmation_token);
    if (!$user) {
        $errors = get_db_errors();
        if ($errors[LOGIN] === "duplicate entity") {
            $errors = ['error' => [EMAIL => "User with this email already registered"]];
        }
        render_conflict($errors);
        return;
    }
    $retries = 0;
    $account = false;
    while (!$account && $retries <= CREATE_ACCOUNT_RETRIES) {
        $retries++;
        $account = payment_create_account($user[ID], DEFAULT_BALANCE);
    }
    send_verification_request_email($email, $_SERVER['HTTP_HOST'], $confirmation_token);
}

function api_auth_logout_action()
{
    delete_token_cookie();
    https_redirect("/");
}

function api_auth_verify_action()
{
    if (array_key_exists(CONFIRMATION_TOKEN, $_GET)) {
        $user = verify_user($_GET[CONFIRMATION_TOKEN]);
        if (!is_null($user)) {
            $token = create_jwt_token($user[EMAIL], $user[ROLE], $user[ID]);
            set_token_cookie($token);
            https_redirect("/");
            send_verification_confirmed_email($user[EMAIL], $_SERVER['HTTP_HOST']);
        } else
            render_not_authorized_json();
    } else {
        render_not_authorized_json();
    }
}

route_request($routes, $authorization);
