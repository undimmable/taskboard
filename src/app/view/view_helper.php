<?php
/**
 * View functions
 *
 * PHP version 5
 *
 * @category  ViewFunctions
 * @package   View
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */

function render_not_found($error = [JSON_ERROR => [UNSPECIFIED => "Not found"]])
{
    render_status_json(404, $error);
}

function render_ok_json($response)
{
    render_status_json(200, $response);
}

function render_ok()
{
    http_response_code(200);
}

function render_unsupported_media_type($error = [JSON_ERROR => [UNSPECIFIED => "Unsupported media type"]])
{
    render_status_json(415, $error);
}

function render_conflict($error)
{
    render_status_json(409, $error);
}

function render_internal_server_error($error = [JSON_ERROR => [UNSPECIFIED => "Something went wrong"]])
{
    render_status_json(500, $error);
}

function render_not_allowed_json($error = [JSON_ERROR => [UNSPECIFIED => "Method not allowed"]])
{
    render_status_json(405, $error);
}


function render_bad_request_json($error)
{
    render_status_json(400, $error);
    ob_flush();
}

function render_not_authorized_json($error = [JSON_ERROR => [UNSPECIFIED => "Not authorized"]])
{
    render_status_json(401, $error);
}

function render_forbidden($error = [JSON_ERROR => [UNSPECIFIED => "Forbidden"]])
{
    render_status_json(403, $error);
}

function render_status_json($code, $message)
{
    http_response_code($code);
    echo json_encode($message);
}