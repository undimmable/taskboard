<?php

function __validate_customer_task_csrf($csrf, $customer_id, $task_id, &$validation_context)
{
    if ($csrf != get_customer_task_csrf($customer_id, $task_id)) {
        add_validation_error($validation_context, 'token', 'wrong');
        return false;
    } else
        return true;
}

function __validate_task_create_csrf($csrf, $customer_id, $task_id, &$validation_context)
{
    if ($csrf != get_customer_task_create_csrf($customer_id, $task_id)) {
        add_validation_error($validation_context, 'token', 'wrong');
        return false;
    } else
        return true;
}

function is_performer_task_csrf_token_valid($csrf, $performer_id, $task_id, &$validation_context)
{
    if ($csrf != get_customer_task_create_csrf($performer_id, $task_id)) {
        add_validation_error($validation_context, 'token', 'wrong');
        return false;
    } else
        return true;
}

function is_login_csrf_token_valid($csrf, &$validation_context)
{
    if ($csrf != get_login_csrf()) {
        add_validation_error($validation_context, 'token', 'wrong');
        return false;
    } else
        return true;
}

function is_signup_csrf_token_valid($csrf, &$validation_context)
{
    if ($csrf != get_signup_csrf()) {
        add_validation_error($validation_context, 'token', 'wrong');
        return false;
    } else
        return true;
}

function is_account_csrf_token_valid($csrf, $user_id, &$validation_context)
{
    if ($csrf != get_account_csrf($user_id)) {
        add_validation_error($validation_context, 'token', 'wrong');
        return false;
    } else
        return true;
}

function is_password_valid($password, &$validation_context)
{
    if (is_null($password)) {
        add_validation_error($validation_context, PASSWORD, 'Password not provided');
        return false;
    }
    if (strlen($password) < get_config_min_password_length()) {
        add_validation_error($validation_context, PASSWORD, 'Password is too short');
        return false;
    }
    return true;
}

function is_password_repeat_valid($password, $password_repeat, &$validation_context)
{
    if (!$password)
        return false;
    if (is_null($password_repeat)) {
        add_validation_error($validation_context, PASSWORD_REPEAT, 'Password repeat not provided');
        return false;
    }
    if ($password !== $password_repeat) {
        add_validation_error($validation_context, PASSWORD, "Passwords don't match");
        add_validation_error($validation_context, PASSWORD_REPEAT, "Passwords don't match");
        return false;
    }
    return true;
}

function is_email_valid($email, &$validation_context)
{
    if (is_null($email)) {
        add_validation_error($validation_context, EMAIL, 'Email not provided');
        return false;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        add_validation_error($validation_context, EMAIL, 'Email is invalid');
        return false;
    }
    if (strlen($email) > get_config_max_email_length()) {
        add_validation_error($validation_context, EMAIL, 'Email is way too long');
        return false;
    }
    return true;
}

function is_ip_valid($ip)
{
    return filter_var($ip, FILTER_VALIDATE_IP);
}

function is_checked($value)
{
    return $value === "on";
}

function is_role_valid($role, &$validation_context)
{
    if (is_null($role)) {
        add_validation_error($validation_context, ROLE, 'User role not provided');
        return false;
    }
    if (!filter_var($role, FILTER_VALIDATE_INT)) {
        add_validation_error($validation_context, ROLE, 'Invalid user role');
        return false;
    }
    if (!role_value_exists($role)) {
        $filtered_role = filter_var(FILTER_SANITIZE_NUMBER_INT);
        add_validation_error($validation_context, ROLE, "No such role $filtered_role");
        return false;
    }
    return true;
}

function add_validation_error(&$validation_context, $name, $description)
{
    $validation_context[$name] = $description;
}

function get_validation_error(&$validation_context, $name)
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