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
require_once "../lib/mail.php";
require_once "../dal/user.php";

$routes = [
    'POST' => [
        'login' => 'auth_login_action',
        'signup' => 'auth_signup_action',
    ],
    'GET' => [
        'verify' => 'auth_verify_action',
        'logout' => 'auth_logout_action',
        'signup_vk' => 'auth_signup_vk_action'
    ],
    'PUT' => [],
    'DELETE' => []
];

function __validate_signup_input($email, $role, $password, $password_repeat)
{
    $validation_context = initialize_validation_context();
    is_email_valid($email, $validation_context);
    is_role_valid($role, $validation_context);
    is_password_valid($password, $validation_context);
    is_password_repeat_valid($password, $password_repeat, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        header('Content-Type: application/json');
        render_bad_request(get_all_validation_errors($validation_context));
        die;
    }
}

function __validate_login_input($email)
{
    $validation_context = initialize_validation_context();
    is_email_valid($email, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        render_bad_request(get_all_validation_errors($validation_context));
        die;
    }
}

function auth_login_action()
{
    if (is_request_www_form()) {
        $data = $_POST;
    } elseif (is_request_json()) {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        render_unsupported_media_type();
        die;
    }
    $email = $data[EMAIL];
    $password = $data[PASSWORD];
    $remember_me = is_checked($data[REMEMBER_ME]);
    __validate_login_input($email);
    $user = db_fetch_user_by_email($email);
    if ($user === null || !password_verify($password, $user["hashed_password"])) {
        render_not_found([
            'error' => [EMAIL => "Wrong username and/or password"]
        ], "application/json");
    }
    $token = create_jwt_token($user[EMAIL], $user[ROLE], $user[ID]);
    set_token_cookie($token, !$remember_me);
    render_ok("application/json", "/feed");
}

function auth_signup_action()
{
    if (is_request_www_form()) {
        $email = $_POST[EMAIL];
        $is_customer = $_POST[IS_CUSTOMER];
        $password = $_POST[PASSWORD];
        $password_repeat = $_POST[PASSWORD_REPEAT];
    } elseif (is_request_json()) {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data[EMAIL];
        $is_customer = $data[IS_CUSTOMER];
        $password = $data[PASSWORD];
        $password_repeat = $data[PASSWORD_REPEAT];
    } else {
        render_unsupported_media_type();
        die;
    }
    if (is_checked($is_customer)) {
        $role = get_role_key("Customer");
    } else {
        $role = get_role_key("Performer");
    }
    __validate_signup_input($email, $role, $password, $password_repeat);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $confirmation_token = create_confirmation_token($email);
    $user = create_user($email, $role, $hashed_password, $confirmation_token);
    if (!$user) {
        $db_errors = get_db_errors();
        if ($db_errors[LOGIN] === "duplicate entity") {
            render_conflict([
                'error' => [EMAIL => "User with this email already registered"]
            ]);
        } else {
            render_conflict($db_errors);
        }
    }
    $token = create_jwt_token($email, $role, $user[ID]);
    set_token_cookie($token);
    send_verification_request_email($email, $_SERVER['HTTP_HOST'], $confirmation_token);
    render_ok("application/json", "/");
}

function auth_logout_action()
{
    if (delete_token_cookie())
        https_redirect("/");
    else
        render_not_authorized();
}

function auth_verify_action()
{
    if (isset($_GET['confirmation_token'])) {
        $user = verify_user($_GET['confirmation_token']);
        if (!is_null($user)) {
            $token = create_jwt_token($user[EMAIL], $user[ROLE], $user[ID]);
            set_token_cookie($token);
            send_verification_confirmed_email($user[EMAIL], $_SERVER['HTTP_HOST']);
            render_ok("text/html", "/");
        } else
            render_not_authorized("text/html");
    } else {
        render_not_authorized("text/html");
    }
}

route_request($routes);