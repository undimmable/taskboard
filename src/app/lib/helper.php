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