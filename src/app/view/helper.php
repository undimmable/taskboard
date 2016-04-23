<?php

function render_not_found($content_type = "text/html")
{
    http_response_code(404);
    if ($content_type !== "application/json") {
        include $_SERVER['DOCUMENT_ROOT'] . '/../public/html/http_errors/404.html';
        die;
    }
}

function add_html_header()
{
    add_template('header', $_SERVER["CONTENT_TYPE"]);
}

function add_html_footer()
{
    add_template('footer', $_SERVER["CONTENT_TYPE"]);
}

function add_template($template_name, $content_type = "application/json")
{
    if ($content_type !== "application/json") {
        include "templates/$template_name.html.php";
    }
}