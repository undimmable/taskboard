<?php
function add_html_header()
{
    add_static_template('header', $_SERVER["CONTENT_TYPE"]);
}

function add_html_footer()
{
    add_static_template('footer', $_SERVER["CONTENT_TYPE"]);
}

function add_static_template($template_name, $content_type = "application/json")
{
    if ($content_type !== "application/json") {
        include "templates/$template_name.html";
    }
}

function add_dynamic_template($template_name, $content_type = "application/json")
{
    if ($content_type !== "application/json") {
        include "templates/$template_name.php";
    }
}