<?php
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

function parse_ip()
{
    return inet_pton($_SERVER['REMOTE_ADDR']);
}

function parse_user_client()
{
    $agent = $_SERVER['HTTP_USER_AGENT'];
    if (strlen($agent <= 256))
        return $agent;
    else
        return substr($agent, 0, 255);
}

function is_task_active($task)
{
    return array_key_exists(LOCK_TX_ID, $task) && ($task[LOCK_TX_ID] != -1);
}

function is_task_completed($task)
{
    return array_key_exists(PERFORMER_ID, $task) && $task[PERFORMER_ID] != null;
}

function get_performer_img($performer_id)
{
    return "/img/m.png";
}

function get_default_img()
{
    return "/icons/favicon-96x96.png";
}

function get_task_img($task)
{
    if (array_key_exists(PERFORMER_ID, $task) && $task[PERFORMER_ID] != null)
        return get_performer_img($task[PERFORMER_ID]);
    else
        return get_default_img();
}