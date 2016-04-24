<?php
$user = null;

function create_token($email, $role, $id)
{
    global $jwt_key;
    return JWT_encode([get_username_assoc_key() => $email, get_role_assoc_key() => $role, "id" => $id], $jwt_key);
}

function set_token_cookie($jwt_token, $session = true)
{
    $expire = $session ? 0 : time() + 60 * 60 * 24 * 365;
    setcookie("PRIVATE-TOKEN", $jwt_token, $expire, "/", null, true, true);
}

function delete_token_cookie()
{
    if (is_authorized()) {
        setcookie(get_token_assoc_key(), null, 0, "/", null, true, true);
        return true;
    } else
        return false;
}

function parse_token_from_cookie()
{
    global $jwt_key;
    $privateToken = $_COOKIE[get_token_assoc_key()];
    if (is_null($privateToken))
        return null;
    return JWT_decode($privateToken, $jwt_key);
}

function try_authorize_from_cookie()
{
    if (!is_null(get_authorized_user()))
        return;
    $token = parse_token_from_cookie();
    if (is_null($token))
        return;
    set_authorized_user(parse_user_from_token($token));
}

function parse_user_from_token($token)
{
    if (!array_key_exists(get_username_assoc_key(), $token) || !array_key_exists(get_role_assoc_key(), $token) || !array_key_exists('id', $token))
        return null;
    $username = $token[get_username_assoc_key()];
    if (is_null($username))
        return null;
    $role = $token[get_role_assoc_key()];
    if (is_null($role))
        return null;
    $id = $token['id'];
    if (is_null($id))
        return null;
    return [get_username_assoc_key() => $username, get_role_assoc_key() => $role, 'id' => $id];
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
    global $confirmation_key;
    return hash("sha256", $string . $confirmation_key);
}