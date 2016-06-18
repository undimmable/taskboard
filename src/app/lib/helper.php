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
function https_redirect($uri, $back = null)
{
    $host = $_SERVER['HTTP_HOST'];
    if (!is_null($back)) {
        $uri = "$uri?$back";
    }
    header("Location: https://$host$uri", true, 301);
}

function is_get()
{
    return $_SERVER["REQUEST_METHOD"] === "GET";
}

function is_post()
{
    return $_SERVER["REQUEST_METHOD"] === "POST";
}

function is_put()
{
    return $_SERVER["REQUEST_METHOD"] === "PUT";
}

function is_delete()
{
    return $_SERVER["REQUEST_METHOD"] === "DELETE";
}

function get_request_content_type()
{
    return $_SERVER["CONTENT_TYPE"];
}

function is_request_www_form()
{
    $content_type = get_request_content_type();
    return strpos($content_type, 'multipart/form-data') !== false || strpos($content_type, 'application/x-www-form-urlencoded') !== false;
}

function is_request_json()
{
    $content_type = get_request_content_type();
    return strpos($content_type, 'application/json') !== false;
}

function array_slice_assoc($array, $keys)
{
    return array_intersect_key($array, array_flip($keys));
}

function parse_integer_param($param_name)
{
    if (!array_key_exists($param_name, $_GET))
        return null;
    $param = $_GET[$param_name];
    if ($param !== null) {
        $param = filter_var($param, FILTER_VALIDATE_INT);
        if ($param === false)
            $param = null;
    }
    return $param;
}

function parse_ip($ip)
{
    return inet_pton($ip);
}

function parse_ip_from_server()
{
    return parse_ip($_SERVER['REMOTE_ADDR']);
}

function parse_user_client_from_server()
{
    return parse_user_client($_SERVER['HTTP_USER_AGENT']);
}

function parse_user_client($agent)
{
    if (strlen($agent <= 256))
        return $agent;
    else
        return substr($agent, 0, 255);
}

function is_task_active($task)
{
    return array_key_exists(BALANCE_LOCKED, $task) && ($task[BALANCE_LOCKED]);
}

function is_task_completed($task)
{
    return array_key_exists(PERFORMER_ID, $task) && $task[PERFORMER_ID] != null;
}

function get_customer_task_csrf($user_id, $task_id)
{
    if ($GLOBALS['staging']) {
        return 10;
    }
    return hash("sha256", "$user_id.$task_id." . get_config_task_csrf_secret(), false);
}

function get_customer_task_create_csrf($user_id, $task_id)
{
    if ($GLOBALS['staging']) {
        return 10;
    }
    return hash("sha256", "$user_id.$task_id." . get_config_task_csrf_secret(), false);
}

function get_event_token_header()
{
    return "HTTP_X_EVENT_SECRET: " . get_config_events_secret();
}

function get_performer_task_csrf($user_id, $task_id)
{
    if ($GLOBALS['staging']) {
        return 10;
    }
    return hash("sha256", "$user_id.$task_id." . get_config_task_csrf_secret(), false);
}

function get_login_csrf()
{
    if ($GLOBALS['staging']) {
        return 9;
    }
    return hash("sha256", 0 . " ." . parse_ip_from_server() . "." . parse_user_client_from_server() . ".gilon" . get_config_login_csrf_secret(), false);
}

function get_signup_csrf()
{
    if ($GLOBALS['staging']) {
        return 8;
    }
    return hash("sha256", 0 . "." . parse_ip_from_server() . "." . parse_user_client_from_server() . ".gnsiup" . get_config_login_csrf_secret(), false);
}

function get_account_csrf($user_id)
{
    if ($GLOBALS['staging']) {
        return 11;
    }
    return hash("sha256", ".$user_id.account" . get_config_account_csrf_secret(), false);
}

function get_performer_img()
{
    return "/img/m.png";
}

function get_customer_img()
{
    return "/img/w.png";
}

function get_system_img()
{
    return "/icons/favicon-96x96.png";
}

function get_last_event_id()
{
    require_once 'dal/event.php';
    $dal_last_event_id = dal_last_event_id();
    if ($dal_last_event_id && array_key_exists('id', $dal_last_event_id))
        return $dal_last_event_id['id'];
    else
        return -1;
}

function get_task_img($task, $user)
{
    if (is_task_completed($task)) {
        return get_performer_img();
    } else {
        if (is_customer($user[ROLE])) {
            return get_system_img();
        } else {
            return get_customer_img();
        }
    }
}

function get_system_commission()
{
    return SYSTEM_COMMISSION_PERCENT;
}
