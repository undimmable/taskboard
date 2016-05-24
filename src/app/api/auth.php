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

function __validate_signup_input($email, $role, $password, $password_repeat)
{
    $validation_context = initialize_validation_context();
    is_email_valid($email, $validation_context);
    is_role_valid($role, $validation_context);
    is_password_valid($password, $validation_context);
    is_password_repeat_valid($password, $password_repeat, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        header('Content-Type: application/json');
        render_bad_request_json(['error' => get_all_validation_errors($validation_context)]);
        die;
    }
}

function __validate_login_input($email, $password)
{
    $validation_context = initialize_validation_context();
    is_email_valid($email, $validation_context);
    is_password_valid($password, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        render_bad_request_json(['error' => get_all_validation_errors($validation_context)]);
        die;
    }
}

function api_auth_login_action()
{
    $data = null;
    if (is_request_www_form()) {
        $data = $_POST;
    } elseif (is_request_json()) {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        render_unsupported_media_type();
        return;
    }
    $email = $data[EMAIL];
    $password = $data[PASSWORD];
    $remember_me = is_checked($data[REMEMBER_ME]);
    __validate_login_input($email, $password);
    $user = db_fetch_user_by_email($email);
    if ($user === null || !password_verify($password, $user[HASHED_PASSWORD])) {
        render_bad_request_json([
            'error' => [EMAIL => 'Wrong username and/or password'],
            'event' => [
                [
                    'local_storage' => [
                        [
                            'key' => 'error-popup',
                            'value' => 'Login error'
                        ]
                    ]
                ]
            ]
        ]);
        die();
    }
    $token = create_jwt_token($user[EMAIL], $user[ROLE], $user[ID]);
    set_token_cookie($token, !$remember_me);
    render_ok_json([
        'redirect' => '/',
        'event' => [
            [
                'local_storage' => [
                    [
                        'key' => 'success-popup',
                        'value' => 'Login successful'
                    ]
                ]
            ]
        ]
    ]);
}

function api_auth_signup_action()
{
    $email = $is_customer = $password = $password_repeat = null;
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
    }
    if (is_checked($is_customer)) {
        $role = get_role_key(CUSTOMER);
    } else {
        $role = get_role_key(PERFORMER);
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
}

function api_auth_logout_action()
{
    if (delete_token_cookie())
        https_redirect("/");
    else
        render_not_authorized_json();
}

function api_auth_verify_action()
{
    if (isset($_GET[CONFIRMATION_TOKEN])) {
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

route_request($routes);
