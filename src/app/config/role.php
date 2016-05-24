<?php

$roles = array(1 => SYSTEM, 2 => CUSTOMER, 3 => PERFORMER);
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

function is_customer($user)
{
    global $roles_reversed;
    return $user[ROLE] === $roles_reversed[CUSTOMER];
}

function is_performer($key)
{
    global $roles_reversed;
    return $key === $roles_reversed[PERFORMER];
}