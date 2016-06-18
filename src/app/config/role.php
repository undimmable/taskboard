<?php
/**
 * Role definitions
 *
 * PHP version 5
 *
 * @category  ConfigFunctions
 * @package   Config
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */

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

function is_system($key)
{
    global $roles_reversed;
    return $key === $roles_reversed[SYSTEM];
}

function auth_unauthenticated()
{
    return 0;
}

function auth_any_authenticated()
{
    return get_role_key(SYSTEM) + get_role_key(CUSTOMER) + get_role_key(PERFORMER);
}

function auth_check_authorization($required_level)
{
    $user = get_authorized_user();
    if (is_null($user)) {
        if ($required_level === 0)
            return true;
        else
            return false;
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
    return ($role & $required_level) != 0;
}
