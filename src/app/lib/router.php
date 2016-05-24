<?php

function route_request($routes)
{
    ob_start();
    $request_method = $_SERVER["REQUEST_METHOD"];
    if (is_null($request_method) || !isset($routes[$request_method]))
        render_not_allowed_json();
    $path_param = $_REQUEST['path_param'];

    if (!is_null($path_param)) {
        if (array_key_exists($path_param, $routes[$request_method])) {
            if (function_exists($routes[$request_method][$path_param])) {
                return call_user_func($routes[$request_method][$path_param]);
            }
        } else if (array_key_exists('/\d+/', $routes[$request_method])) {
            if (preg_match('/\d+/', $path_param)) {
                $id = $path_param;
                if (function_exists($routes[$request_method]['/\d+/'])) {
                    return call_user_func($routes[$request_method]['/\d+/'], $id);
                }
            }
        }
    }
    render_not_found();
    ob_end_flush();
    die();
}