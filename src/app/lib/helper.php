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