<?php

function render_not_found($error_description = null, $content_type = "text/html")
{
    http_response_code(404);
    if ($content_type !== "application/json") {
        /** @noinspection PhpIncludeInspection */
        include $_SERVER['DOCUMENT_ROOT'] . '/../public/html/http_errors/404.html';
    } else {
        header('Content-Type: application/json');
        echo json_encode($error_description);
    }
    die;
}

function render_ok($content_type = "application/json", $redirect = null)
{
    http_response_code(200);
    if ($redirect != null) {
        if ($content_type === "application/json")
            echo json_encode(["redirect" => $redirect]);
        else
            https_redirect($redirect);
    }
    die;
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
    } else {
        header('Content-Type: application/json');
        echo json_encode($error_description);
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
        echo "Not authorized";
    } else {
        echo json_encode(["error" => "Not authorized"]);
    }
    die;
}

function render_bad_request($error, $content_type = "application/json")
{
    http_response_code(400);
    if ($content_type !== "application/json") {
        echo $error;
    } else {
        header('Content-Type: application/json');
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

function add_login_buttons()
{
    include "templates/login_buttons.html.php";
}

function add_logout_button()
{
    include "templates/logout_button.html.php";
}

function add_html_footer($content_type = "text/html")
{
    if ($content_type !== "application/json") {
        include "templates/footer.html.php";
    }
}

function add_html_task($task, $content_type = "text/html")
{
    if ($content_type !== "application/json") {
        include "templates/task.html.php";
    }
}

function add_login_form($content_type = "text/html")
{
    if ($content_type !== "application/json") {
        include "templates/login_form.html.php";
    }
}

function add_signup_form($content_type = "text/html")
{
    if ($content_type !== "application/json") {
        include "templates/signup_form.html.php";
    }
}

function signin_vk_url()
{
    $vk_client_id = get_config_vk_client_id();
    $vk_redirect_uri = "https://taskboard.dev/api/v1/auth/signup_vk";
    return "http://oauth.vk.com/authorize?client_id=$vk_client_id&redirect_uri=$vk_redirect_uri&response_type=code&scope=email";
}