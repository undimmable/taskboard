<?php

function is_password_valid($password, &$validation_context)
{
    if (is_null($password)) {
        add_validation_error($validation_context, 'password', 'not provided');
        return false;
    }
    if (strlen($password) < get_minimal_password_length()) {
        add_validation_error($validation_context, 'password', 'too short');
        return false;
    }
    return true;
}

function is_email_valid($email, &$validation_context)
{
    if (is_null($email)) {
        add_validation_error($validation_context, 'email', 'not provided');
        return false;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        add_validation_error($validation_context, 'email', 'is invalid');
        return false;
    }
    return true;
}

function is_role_valid($role, &$validation_context)
{
    if (is_null($role)) {
        add_validation_error($validation_context, 'role', 'not provided');
        return false;
    }
    if (!filter_var($role, FILTER_VALIDATE_INT)) {
        add_validation_error($validation_context, 'role', 'is invalid');
        return false;
    }
    if (!role_exists($role)) {
        $filtered_role = filter_var(FILTER_SANITIZE_NUMBER_INT);
        add_validation_error($validation_context, 'role', "no such role $filtered_role");
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