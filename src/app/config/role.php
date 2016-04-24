<?php

$roles = array(1 => "system", 2 => "customer", 3 => "performer");

function get_role($key)
{
    global $roles;
    return $roles[$key];
}

function role_exists($key)
{
    global $roles;
    return array_key_exists($key, $roles);
}