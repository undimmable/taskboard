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
 * Bootstrap application
 */
require_once "../bootstrap.php";
require_once "../lib/mail.php";
require_once "../dal/user.php";

$routes = [
    'POST' => [
        'login' => 'auth_login_action',
        'logout' => 'auth_logout_action',
        'signup' => 'auth_signup_action',
    ],
    'GET' => [
        'verify' => 'auth_verify_action',
        'signup_vk' => 'auth_signup_vk_action'
    ],
    'PUT' => [],
    'DELETE' => []
];

function __validate_signup_input($email, $role, $password)
{
    $validation_context = initialize_validation_context();
    is_email_valid($email, $validation_context);
    is_role_valid($role, $validation_context);
    is_password_valid($password, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        echo json_encode(get_all_validation_errors($validation_context));
        die;
    }
}

function __validate_login_input($email, $role)
{
    $validation_errors = initialize_validation_context();
    is_email_valid($email, $validation_errors);
    is_role_valid($role, $validation_errors);
    if (validation_context_has_errors($validation_errors)) {
        echo json_encode(get_all_validation_errors($validation_errors));
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
    $email = $data["email"];
    $password = $data["password"];
    $user = db_fetch_user_by_email($email);
    if ($user === null)
        render_not_found();
    $hashed_password = $user["hashed_password"];
    if (!password_verify($password, $hashed_password))
        render_not_found();
    $token = create_token($user["email"], $user["role"], $user['id']);
    set_token_cookie($token);
    render_ok();
}

function auth_signup_action()
{
    if (is_request_www_form()) {
        $email = $_POST[USER_EMAIL];
        $role = $_POST[USER_ROLE];
        $password = $_POST[USER_PASSWORD];
    } elseif (is_request_json()) {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data[USER_EMAIL];
        $role = $data[USER_ROLE];
        $password = $data[USER_PASSWORD];
    } else {
        render_unsupported_media_type();
        die;
    }
    __validate_signup_input($email, $role, $password);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $confirmation_token = create_confirmation_token($email);
    $user = create_user($email, $role, $hashed_password, $confirmation_token);
    if (!$user) {
        $db_errors = get_db_errors();
        render_conflict($db_errors[LOGIN]);
    }
    $token = create_token($email, $role, $user[ID]);
    set_token_cookie($token);
    send_verification_request_email($email, $confirmation_token);
    flush();
}

function auth_logout_action()
{
    if (delete_token_cookie())
        render_ok();
    else
        render_not_authorized();
}

function auth_verify_action()
{
    if (isset($_GET['confirmation_token'])) {
        $user = verify_user($_GET['confirmation_token']);
        if (!is_null($user)) {
            send_verification_confirmed_email($user[USER_EMAIL]);
            https_redirect("/");
        } else
            render_not_authorized();
    } else {
        render_not_authorized();
    }
}

route_request($routes);