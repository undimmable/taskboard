<?php
/**
 * Security functions
 *
 * PHP version 5
 *
 * @category  SecurityFunctions
 * @package   Security
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */

$user = null;
require_once 'dal/login.php';
require_once 'lib/helper.php';

function create_jwt_token($email, $role, $id)
{
    return JWT_encode([EMAIL => $email, ROLE => $role, ID => $id], get_config_jwt_secret());
}

function set_token_cookie($jwt_token, $session = true)
{
    $expire = $session ? 0 : time() + 60 * 60 * 24 * 365;
    setcookie(PRIVATE_TOKEN, $jwt_token, $expire, "/", null, true, true);
}

function delete_token_cookie()
{
    setcookie(PRIVATE_TOKEN, null, 0, "/", null, true, true);
}

function parse_token_from_cookie()
{
    if (!array_key_exists(PRIVATE_TOKEN, $_COOKIE))
        return null;
    $privateToken = $_COOKIE[PRIVATE_TOKEN];
    if (is_null($privateToken))
        return null;
    return JWT_decode($privateToken, get_config_jwt_secret());
}

function try_authenticate_from_cookie()
{
    if (!is_null(get_authorized_user()))
        return;
    $token = parse_token_from_cookie();
    if (is_null($token))
        return;
    $user = parse_user_from_token($token);
    $user_id = $user[ID];
    $login = dal_login_fetch($user_id, parse_ip(), parse_user_client());
    if (is_null($login) || !$login) {
        delete_token_cookie();
    } else {
        set_authorized_user($user);
    }
}

function parse_csrf_token_header()
{
    if (array_key_exists('HTTP_X_CSRF_TOKEN', $_SERVER))
        return $_SERVER['HTTP_X_CSRF_TOKEN'];
    else
        return null;
}

function parse_user_from_token($token)
{
    if (!array_key_exists(EMAIL, $token) || !array_key_exists(ROLE, $token) || !array_key_exists(ID, $token))
        return null;
    $username = $token[EMAIL];
    if (is_null($username))
        return null;
    $role = $token[ROLE];
    if (is_null($role))
        return null;
    $id = $token[ID];
    if (is_null($id))
        return null;
    return [EMAIL => $username, ROLE => $role, ID => $id];
}

function get_authorized_user()
{
    global $user;
    return $user;
}

function set_authorized_user($authorized_user)
{
    global $user;
    $user = $authorized_user;
}

function is_authorized()
{
    return !is_null(get_authorized_user());
}

function create_confirmation_token($string)
{
    return hash("sha256", $string . get_config_confirmation_secret());
}