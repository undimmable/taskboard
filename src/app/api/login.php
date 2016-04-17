<?php
session_start();
require_once "../bootstrap.php";
if (is_get()) {
    add_html_header();
    $redirect_back = "/";
    add_dynamic_template("login_form");
    add_html_footer();
}

if (is_post()) {
    echo $_POST["foo"];
}