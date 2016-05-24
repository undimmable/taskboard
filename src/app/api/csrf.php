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
        'signup' => 'csrf_signup'
    ]
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

route_request($routes);
