<?php

$user_input_validation_errors = [];

function is_password_valid($password)
{
    global $minimal_password_length, $user_input_validation_errors;
    if (is_null($password)) {
        $user_input_validation_errors["password"] = "not provided";
        return false;
    }
    if (strlen($password) < $minimal_password_length) {
        $user_input_validation_errors["password"] = "too short";
        return false;
    }
    return true;
}

function is_email_valid($email)
{
    global $user_input_validation_errors;
    if (is_null($email)) {
        $user_input_validation_errors["email"] = "not provided";
        return false;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $user_input_validation_errors["email"] = "is invalid";
        return false;
    }
    return true;
}

function is_role_valid($role)
{
    global $user_input_validation_errors;
    if (is_null($role)) {
        $user_input_validation_errors["role"] = "not provided";
        return false;
    }
    if (!filter_var($role, FILTER_VALIDATE_INT)) {
        $user_input_validation_errors["role"] = "is invalid";
        return false;
    }
    if (!role_exists($role)) {
        $filtered_role = filter_var(FILTER_SANITIZE_NUMBER_INT);
        $user_input_validation_errors["role"] = "no such role $filtered_role";
        return false;
    }
    return true;
}

function user_input_has_errors()
{
    global $user_input_validation_errors;
    return !empty($user_input_validation_errors);
}

function initialize_user_input_validation()
{
    global $user_input_validation_errors;
    $user_input_validation_errors = [];
}

function get_user_input_validation_errors()
{
    global $user_input_validation_errors;
    return $user_input_validation_errors;
}