<?php
/**
 * Lib functions
 *
 * PHP version 5
 *
 * @category  LibFunctions
 * @package   Lib
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */
function route_request($routes, $authorization = null)
{
    ob_start();
    $rendered = false;
    $request_method = $_SERVER["REQUEST_METHOD"];
    if (is_null($request_method) || !isset($routes[$request_method]))
        render_not_allowed_json();
    $path_param = $_REQUEST['path_param'];

    if (!is_null($path_param)) {
        if (array_key_exists($path_param, $routes[$request_method])) {
            $func_name = $routes[$request_method][$path_param];
            if (function_exists($func_name)) {
                if (array_key_exists($func_name, $authorization)) {
                    try_authenticate_from_cookie();
                    if (!auth_check_authorization($authorization[$func_name]))
                        render_forbidden();
                    else
                        call_user_func($func_name);
                    $rendered = true;
                } else {
                    error_log(sprintf("Function %s exists but no authorization defined", $func_name));
                }
            } else {
                error_log(sprintf("Function %s not exists", $func_name));
            }
        } else if (array_key_exists('/\d+/', $routes[$request_method])) {
            if (preg_match('/\d+/', $path_param)) {
                $id = $path_param;
                $func_name = $routes[$request_method]['/\d+/'];
                if (function_exists($func_name)) {
                    if (array_key_exists($func_name, $authorization)) {
                        try_authenticate_from_cookie();
                        if (!auth_check_authorization($authorization[$func_name]))
                            render_forbidden();
                        else
                            call_user_func($func_name, $id);
                        $rendered = true;
                    } else {
                        error_log(sprintf("Function %s exists but no authorization defined", $func_name));
                    }
                } else {
                    error_log(sprintf("Function %s not exists", $func_name));
                }
            }
        }
    }
    if (!$rendered)
        render_not_found();
    ob_end_flush();
    die;
}