<?php
/**
 * Lib functions
 *
 * PHP version 5
 *
 * @category  LibFunctions
 * @package   Lib
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */
function _validate_customer_task_csrf($csrf, $customer_id, $task_id, &$validation_context)
{
    if ($csrf != get_customer_task_csrf($customer_id, $task_id)) {
        _add_validation_error($validation_context, 'unspecified', 'token_wrong');
        return false;
    } else
        return true;
}

function _validate_task_create_csrf($csrf, $customer_id, $task_id, &$validation_context)
{
    if ($csrf != get_customer_task_create_csrf($customer_id, $task_id)) {
        _add_validation_error($validation_context, 'unspecified', 'token_wrong');
        return false;
    } else
        return true;
}

function _validate_performer_task_csrf($csrf, $performer_id, $task_id, &$validation_context)
{
    if ($csrf != get_performer_task_csrf($performer_id, $task_id)) {
        _add_validation_error($validation_context, 'unspecified', 'token_wrong');
        return false;
    } else
        return true;
}

function _is_login_csrf_token_valid($csrf, &$validation_context)
{
    if ($csrf != get_login_csrf()) {
        _add_validation_error($validation_context, 'unspecified', 'token_wrong');
        return false;
    } else
        return true;
}

function _is_signup_csrf_token_valid($csrf, &$validation_context)
{
    if ($csrf != get_signup_csrf()) {
        _add_validation_error($validation_context, 'unspecified', 'token_wrong');
        return false;
    } else
        return true;
}

function _is_remind_csrf_token_valid($csrf, &$validation_context)
{
    if ($csrf != get_reset_password_csrf()) {
        _add_validation_error($validation_context, 'unspecified', 'token_wrong');
        return false;
    } else
        return true;
}

function _is_change_password_token_valid($csrf, &$validation_context)
{
    if ($csrf != get_change_password_csrf()) {
        _add_validation_error($validation_context, 'unspecified', 'token_wrong');
        return false;
    } else
        return true;
}

function is_account_csrf_token_valid($csrf, $user_id, &$validation_context)
{
    if ($csrf != get_account_csrf($user_id)) {
        _add_validation_error($validation_context, 'unspecified', 'token_wrong');
        return false;
    } else
        return true;
}

function _is_password_valid($password, &$validation_context)
{
    if (!$password || is_null($password)) {
        _add_validation_error($validation_context, PASSWORD, 'not_provided');
        return false;
    }
    if (strlen($password) < get_config_min_password_length()) {
        _add_validation_error($validation_context, PASSWORD, 'is_too_short');
        return false;
    }
    return true;
}

function _is_password_repeat_valid($password, $password_repeat, &$validation_context)
{
    if (!$password_repeat || is_null($password_repeat)) {
        _add_validation_error($validation_context, PASSWORD_REPEAT, 'not_provided');
        return false;
    }
    if ($password !== $password_repeat) {
        _add_validation_error($validation_context, PASSWORD, "mismatch");
        _add_validation_error($validation_context, PASSWORD_REPEAT, "mismatch");
        return false;
    }
    return true;
}

function _is_change_password_ts_valid(&$ts, &$validation_context)
{
    if (!$ts || is_null($ts) || !filter_var($ts, FILTER_VALIDATE_INT)) {
        _add_validation_error($validation_context, UNSPECIFIED, 'token_wrong');
        return false;
    }
    return true;
}

function _is_change_password_verification_token_valid(&$verification_token, $email, $ts, &$validation_context)
{
    if (!$verification_token || is_null($verification_token) || $verification_token != get_reset_password_verification_token($email, $ts)) {
        _add_validation_error($validation_context, UNSPECIFIED, 'token_wrong');
        return false;
    }
    return true;
}

function _is_email_valid($email, &$validation_context)
{
    if (is_null($email)) {
        _add_validation_error($validation_context, EMAIL, 'not_provided');
        return false;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        _add_validation_error($validation_context, EMAIL, 'is_invalid');
        return false;
    }
    if (strlen($email) > get_config_max_email_length()) {
        _add_validation_error($validation_context, EMAIL, 'is_too_long');
        return false;
    }
    return true;
}

function _is_ip_valid($ip)
{
    return filter_var($ip, FILTER_VALIDATE_IP);
}

function _is_checked($value)
{
    return $value === "on";
}

function _is_role_valid(&$role, &$validation_context)
{
    if (is_null($role)) {
        _add_validation_error($validation_context, ROLE, 'not_provided');
        return false;
    }
    if (!filter_var($role, FILTER_VALIDATE_INT)) {
        _add_validation_error($validation_context, ROLE, 'is_invalid');
        return false;
    }
    if (!role_exists($role)) {
        $role = filter_var(FILTER_SANITIZE_NUMBER_INT);
        _add_validation_error($validation_context, ROLE, "is_invalid");
        return false;
    }
    if (!(is_customer($role) || (is_performer($role)))) {
        _add_validation_error($validation_context, ROLE, "is_invalid");
        return false;
    }
    return true;
}

function _add_validation_error(&$validation_context, $name, $description)
{
    $validation_context[$name] = $description;
}

function _get_validation_error(&$validation_context, $name)
{
    return $validation_context[$name];
}

function get_all_validation_errors(&$validation_context)
{
    return $validation_context;
}

function validation_context_has_errors(&$validation_errors)
{
    return !empty($validation_errors);
}

function &initialize_validation_context()
{
    $validation_context = [];
    return $validation_context;
}
