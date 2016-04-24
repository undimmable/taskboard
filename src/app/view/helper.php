<?php

function render_not_found($content_type = "text/html")
{
    http_response_code(404);
    if ($content_type !== "application/json") {
        /** @noinspection PhpIncludeInspection */
        include $_SERVER['DOCUMENT_ROOT'] . '/../public/html/http_errors/404.html';
    }
    die;
}

function render_ok()
{
    http_response_code(200);
    flush();
}

function render_unsupported_media_type($content_type = "application/json")
{
    http_response_code(415);
    if ($content_type !== "application/json") {
        echo "Unsupported media type";
    }
    die;
}

function render_conflict($error_description, $content_type = "application/json")
{
    http_response_code(409);
    if ($content_type !== "application/json") {
        echo $error_description;
    }
    die;
}

function render_not_allowed($content_type = "application/json")
{
    http_response_code(405);
    if ($content_type !== "application/json") {
        echo "Method not allowed";
    }
    die;
}

function render_not_authorized($content_type = "application/json")
{
    http_response_code(401);
    if ($content_type !== "application/json") {
        echo "Method not allowed";
    }
    die;
}

function render_bad_request($error, $content_type = "application/json")
{
    http_response_code(400);
    if ($content_type !== "application/json") {
        echo $error;
    } else {
        echo json_encode(["error" => $error]);
    }
    die;
}

function add_html_header($content_type = "text/html")
{
    if ($content_type !== "application/json") {
        include "templates/header.html.php";
    }
}

function add_html_navbar($content_type = "text/html")
{
    if ($content_type !== "application/json") {
        include "templates/navbar.html.php";
    }
}

function add_right_menu($content_type = "text/html")
{
    global $user;
    if ($content_type !== "application/json") {
        if (!is_null($user))
            include "templates/right-menu-authorized.html.php";
        else
            include "templates/right-menu-non-authorized.html.php";
    }
}

function add_html_footer($content_type = "text/html")
{
    if ($content_type !== "application/json") {
        include "templates/footer.html.php";
    }
}

function add_login_form($content_type = "text/html")
{
    if ($content_type !== "application/json") {
        include "templates/login_form.html.php";
    }
}