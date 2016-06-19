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
/**
 * Require bootstrap, dal and lib functions
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
        'reset_password' => 'api_auth_reset_password',
        'change_password' => 'api_auth_change_password'
    ],
    'GET' => [
        'logout' => 'api_auth_logout_action'
    ],
    'PUT' => [],
    'DELETE' => []
];

$authorization = [
    'api_auth_login_action' => auth_unauthenticated(),
    'api_auth_change_password' => auth_unauthenticated(),
    'api_auth_reset_password' => auth_unauthenticated(),
    'api_auth_signup_action' => auth_unauthenticated(),
    'api_auth_logout_action' => auth_any_authenticated()
];

/**
 * Validate signup request provided correct values
 *
 * @param $email string
 * @param $role integer
 * @param $password string
 * @param $password_repeat string
 * @param $csrf string
 * @return bool true if validation succeeds and false otherwise
 */
function _validate_signup_input($email, &$role, $password, $password_repeat, $csrf)
{
    $validation_context = initialize_validation_context();
    _is_signup_csrf_token_valid($csrf, $validation_context);
    _is_email_valid($email, $validation_context);
    _is_role_valid($role, $validation_context);
    _is_password_valid($password, $validation_context);
    _is_password_repeat_valid($password, $password_repeat, $validation_context);
    if (!validation_context_has_errors($validation_context)) {
        return true;
    }
    render_bad_request_json([JSON_ERROR => get_all_validation_errors($validation_context)]);
    return false;
}

/**
 * Validate login request provided correct values
 *
 * @param $email string
 * @param $password string
 * @param $csrf string
 * @return bool true if validation succeeds and false otherwise
 */
function _validate_login_input($email, $password, $csrf)
{
    $validation_context = initialize_validation_context();
    _is_login_csrf_token_valid($csrf, $validation_context);
    _is_email_valid($email, $validation_context);
    _is_password_valid($password, $validation_context);
    if (!validation_context_has_errors($validation_context)) {
        return true;
    }
    render_bad_request_json([JSON_ERROR => get_all_validation_errors($validation_context)]);
    return false;
}

function _validate_remind_input($email, $csrf)
{
    $validation_context = initialize_validation_context();
    _is_remind_csrf_token_valid($csrf, $validation_context);
    _is_email_valid($email, $validation_context);
    if (!validation_context_has_errors($validation_context)) {
        return true;
    }
    render_bad_request_json([JSON_ERROR => get_all_validation_errors($validation_context)]);
    return false;
}

function _validate_change_password_input($email, $password, $password_repeat, $ts, $csrf)
{
    $validation_context = initialize_validation_context();
    _is_change_password_token_valid($csrf, $validation_context);
    _is_email_valid($email, $validation_context);
    _is_password_valid($password, $validation_context);
    _is_password_repeat_valid($password, $password_repeat, $validation_context);
    _is_change_password_ts_valid($ts, $validation_context);
    if (!validation_context_has_errors($validation_context)) {
        return true;
    }
    render_bad_request_json([JSON_ERROR => get_all_validation_errors($validation_context)]);
    return false;
}

/**
 * Set authentication cookie and write login information to database
 *
 * @param $user_id integer
 * @param $user_role integer
 * @param $email string
 * @param $ip string
 * @param $client string
 * @param $remember_me boolean
 */
function _login($user_id, $user_role, $email, $ip, $client, $remember_me)
{
    dal_login_create_or_update($user_id, $ip, $client);
    $token = create_jwt_token($email, $user_role, $user_id);
    set_token_cookie($token, !$remember_me);
    render_ok_json(['redirect' => '/']);
}

/**
 * Api submit login form
 */
function api_auth_login_action()
{
    if (!is_request_json()) {
        render_unsupported_media_type();
        return;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data[EMAIL];
    $password = $data[PASSWORD];
    $remember_me = _is_checked($data[REMEMBER_ME]);
    $csrf = parse_csrf_token_header();
    if (!_validate_login_input($email, $password, $csrf)) {
        return;
    }
    $ip = parse_ip_from_server();
    $client = parse_user_client_from_server();
    $interval = get_config_failed_attempt_timeout();
    $failed_attempts = dal_login_being_failed($ip, $client, get_config_failed_attempt_retry(), $interval);
    if ($failed_attempts) {
        render_not_authorized_json([JSON_ERROR => [UNSPECIFIED => "too_many_attempts"]]);
        return;
    }
    $user = dal_fetch_user_by_email($email);
    if ($user === null || !password_verify($password, $user[HASHED_PASSWORD])) {
        dal_login_log_failed($ip, $client);
        render_not_authorized_json([JSON_ERROR => [EMAIL => 'no_such_user']]);
        return;
    }
    _login($user[ID], $user[ROLE], $user[EMAIL], $ip, $client, $remember_me);
}


/**
 * Api submit signup form
 */
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
    if (_is_checked($is_customer)) {
        $role = get_role_key(CUSTOMER);
    } else {
        $role = get_role_key(PERFORMER);
    }
    $csrf = parse_csrf_token_header();
    if (!_validate_signup_input($email, $role, $password, $password_repeat, $csrf)) {
        return;
    }
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $confirmation_token = create_confirmation_token($email);
    $user = dal_create_user($email, $role, $hashed_password, $confirmation_token);
    if (!$user) {
        $errors = get_dal_errors();
        if ($errors[LOGIN] === "duplicate entity") {
            $errors = [JSON_ERROR => [EMAIL => "already_registered"]];
        }
        render_conflict($errors);
        return;
    }
    $retries = 0;
    $account = false;
    while (!$account && $retries <= CREATE_ACCOUNT_RETRIES) {
        $retries++;
        $account = dal_payment_create_account($user[ID], DEFAULT_BALANCE);
    }
    send_registration_email($email, $_SERVER['HTTP_HOST']);
    _login($user[ID], $role, $email, parse_ip_from_server(), parse_user_client_from_server(), true);
}

/**
 * Remove authentication cookie and redirect to index page
 */
function api_auth_logout_action()
{
    delete_token_cookie();
    https_redirect("/");
}

function api_auth_reset_password()
{
    if (!is_request_json()) {
        render_unsupported_media_type();
        return;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data[EMAIL];
    $csrf = parse_csrf_token_header();
    if (_validate_remind_input($email, $csrf)) {
        $user = dal_fetch_user_by_email($email);
        if ($user) {
            $token = JWT_encode([EMAIL => $user[EMAIL], 'ts' => dal_now()], get_config_confirmation_secret());
            send_reset_password_email($user[EMAIL], $_SERVER['HTTP_HOST'], $token);
            render_ok_json("");
        } else {
            render_forbidden();
        }
    }
}

function api_auth_change_password()
{
    if (!is_request_json()) {
        render_unsupported_media_type();
        return;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data) {
        $token = JWT_decode($data['verification_token'], get_config_confirmation_secret());
        if ($token) {
            $email = $token[EMAIL];
            $password = $data[PASSWORD];
            $password_repeat = $data[PASSWORD_REPEAT];
            $timestamp = $token['ts'];
            $csrf = parse_csrf_token_header();
            if (_validate_change_password_input($email, $password, $password_repeat, $timestamp, $csrf)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                if (dal_user_update_password_by_email($email, $hashed_password, $timestamp)) {
                    send_password_changed_email($email, $_SERVER['HTTP_HOST']);
                    render_ok_json("");
                } else {
                    render_bad_request_json([JSON_ERROR => [UNSPECIFIED => "token_wrong"]]);
                }
            }
            return;
        }
    }
    render_forbidden();
}

route_request($routes, $authorization);
