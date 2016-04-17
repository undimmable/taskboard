<?php
$jwt_key = 'key_bootstrap';

function setToken($token)
{
    global $jwt_key;
    setcookie("PRIVATE-TOKEN", JWT_encode($token, $jwt_key), -1, "/", null, true, true);
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
    return getToken() !== null;
}