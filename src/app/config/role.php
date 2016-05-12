<?php

$roles = array(1 => "System", 2 => "Customer", 3 => "Performer");
$roles_reversed = array("System" => 1, "Customer" => 2, "Performer" => 3);

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

function is_customer($key) {
    return $key === 2;
}

function is_performer($key) {
    return $key === 3;
}