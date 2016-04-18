<?php
$jwt_key = 'key_bootstrap';

function setToken($token, $session = true)
{
    global $jwt_key;
    $expire = $session ? 0 : time() + 60 * 60 * 24 * 365;
    setcookie("PRIVATE-TOKEN", JWT_encode($token, $jwt_key), $expire, "/", null, true, true);
}

function deleteToken()
{
    global $jwt_key;
    $privateToken = $_COOKIE['PRIVATE-TOKEN'];
    if (!is_null($privateToken)) {
        $payload = JWT_decode($privateToken, $jwt_key);
        if (!is_null($payload)) {
            setcookie("PRIVATE-TOKEN", null, 0, "/", null, true, true);
            return true;
        }
    }
    return false;
}

function getToken()
{
    global $jwt_key;
    $privateToken = $_COOKIE['PRIVATE-TOKEN'];
    if (is_null($privateToken))
        return null;
    return JWT_decode($privateToken, $jwt_key);
}

function isAuthorized()
{
    $c = getToken() !== null;
    return $c;
}