<?php

function render_not_found()
{
    render_status_json(404, array('error' => 'Not found'));
}

function render_ok_json($response)
{
    render_status_json(200, $response);
}

function render_no_content()
{
    http_response_code(204);
}

function render_unsupported_media_type()
{
    render_status_json(415, array('error' => 'Unsupported media type'));
}

function render_conflict($error)
{
    render_status_json(409, $error);
}

function render_internal_server_error($error)
{
    render_status_json(500, $error);
}

function render_not_allowed_json()
{
    render_status_json(405, array('error' => 'Method not allowed'));
}


function render_bad_request_json($error)
{
    render_status_json(400, $error);
    ob_flush();
}

function render_not_authorized_json()
{
    render_status_json(401, array('error' => 'Not authorized'));
}

function render_status_json($code, $message)
{
    http_response_code($code);
    echo json_encode($message);
}

function signin_vk_url()
{
    $vk_client_id = get_config_vk_client_id();
    $vk_redirect_uri = "https://taskboard.dev/api/v1/auth/signup_vk";
    return "http://oauth.vk.com/authorize?client_id=$vk_client_id&redirect_uri=$vk_redirect_uri&response_type=code&scope=email";
}