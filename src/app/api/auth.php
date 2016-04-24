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

function validate_input($email, $role, $password)
{
    initialize_user_input_validation();
    is_email_valid($email);
    is_role_valid($role);
    is_password_valid($password);
    if (user_input_has_errors()) {
        echo json_encode(get_user_input_validation_errors());
        die;
    }
}

function validate_login_input($email, $role)
{
    initialize_user_input_validation();
    is_email_valid($email);
    is_role_valid($role);
    if (user_input_has_errors()) {
        echo json_encode(get_user_input_validation_errors());
        die;
    }
}

function is_login_action()
{
    return $_REQUEST["method"] === "login";
}

function is_signup_action()
{
    return $_REQUEST["method"] === "signup";
}

function is_verify_action()
{
    return $_REQUEST["method"] === "verify";
}

function is_logout_action()
{
    return $_REQUEST["method"] === "logout";
}

function process_login_action()
{
    if (is_request_www_form()) {
        $email = $_POST[get_username_assoc_key()];
        $password = $_POST[get_password_assoc_key()];
    } elseif (is_request_json()) {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data[get_username_assoc_key()];
        $password = $data[get_password_assoc_key()];
    } else {
        render_unsupported_media_type();
        die;
    }
    $user = get_user_by_email($email);
    if ($user === null)
        render_not_found();
    $hashed_password = $user["password"];
    if (!password_verify($password, $hashed_password))
        render_not_found();
    $token = create_token($email, $user[get_username_assoc_key()], $user['id']);
    set_token_cookie($token);
    render_ok();
}

function process_signup_action()
{
    if (is_request_www_form()) {
        $email = $_POST[get_username_assoc_key()];
        $role = $_POST[get_role_assoc_key()];
        $password = $_POST[get_password_assoc_key()];
    } elseif (is_request_json()) {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data[get_username_assoc_key()];
        $role = $data[get_role_assoc_key()];
        $password = $data[get_password_assoc_key()];
    } else {
        render_unsupported_media_type();
        die;
    }
    validate_input($email, $role, $password);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $confirmation_token = create_confirmation_token($email);
    $user = create_user($email, $role, $hashed_password, $confirmation_token);
    if (!$user) {
        $db_errors = get_db_errors();
        render_conflict($db_errors[get_login_entity_name()]);
    }
    $token = create_token($email, $role, $user['id']);
    set_token_cookie($token);
    send_verification_request_email($email, $confirmation_token);
    flush();
}

function process_logout_action()
{
    if (delete_token_cookie())
        render_ok();
    else
        render_not_authorized();
}

function process_verify_action()
{
    if (isset($_GET['confirmation_token'])) {
        $user = verify_user($_GET['confirmation_token']);
        if (!is_null($user)) {
            send_verification_confirmed_email($user[get_username_assoc_key()]);
            https_redirect("/");
        } else
            render_not_authorized();
    } else {
        render_not_authorized();
    }
}

function is_action_allowed()
{
    if (!is_post()) {
        return is_get() && is_verify_action();
    }
    if (is_authorized())
        return is_logout_action();
    else
        return is_login_action() || is_signup_action();
}

function process_request()
{
    if (!is_action_allowed()) {
        render_not_allowed();
    }
    if (is_login_action()) {
        process_login_action();
    } elseif (is_signup_action()) {
        process_signup_action();
    } elseif (is_logout_action()) {
        process_logout_action();
    } elseif (is_verify_action()) {
        process_verify_action();
    } else {
        render_not_allowed();
    }
}

process_request();