<?php
/**
 * @author dimyriy
 * @version 1.0
 */

require_once "../bootstrap.php";
$routes = [
    'GET' => [
        'task' => 'csrf_task',
        'login' => 'csrf_login',
        'signup' => 'csrf_signup',
        'account' => 'csrf_account',
    ]
];

$authorization = [
    'csrf_task' => get_role_key(CUSTOMER) + get_role_key(PERFORMER),
    'csrf_login' => auth_unauthenticated(),
    'csrf_signup' => auth_unauthenticated(),
    'csrf_account' => get_role_key(CUSTOMER)
];

function csrf_task()
{
    echo 10;
}

function csrf_login()
{
    echo 9;
}

function csrf_signup()
{
    echo 8;
}

function csrf_account()
{
    echo 7;
}

route_request($routes, $authorization);
