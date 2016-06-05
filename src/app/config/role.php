<?php

$roles = array(1 => SYSTEM, 2 => CUSTOMER, 4 => PERFORMER);
$roles_reversed = array_flip($roles);

function get_role_value($key)
{
    global $roles;
    return $roles[$key];
}

function get_role_key($role)
{
    global $roles_reversed;
    return $roles_reversed[$role];
}

function role_key_exists($key)
{
    global $roles;
    return array_key_exists($key, $roles);
}

function role_value_exists($key)
{
    global $roles;
    return array_key_exists($key, $roles);
}

function is_customer($key)
{
    global $roles_reversed;
    return $key === $roles_reversed[CUSTOMER];
}

function is_performer($key)
{
    global $roles_reversed;
    return $key === $roles_reversed[PERFORMER];
}

function auth_unauthenticated()
{
    return 0;
}

function auth_any_authenticated()
{
    return 1 + 2 + 4;
}

function auth_check_authorization($required_level)
{
    $user = get_authorized_user();
    if (is_null($user)) {
        if ($required_level === 0)
            return true;
        else
            return null;
    } else {
        if ($required_level === 0)
            return false;
        else {
            return auth_authorized($required_level, $user[ROLE]);
        }
    }
}

function auth_authorized($required_level, $role)
{
    return $role & $required_level != 0;
}