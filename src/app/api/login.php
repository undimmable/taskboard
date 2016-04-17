<?php
require_once "../bootstrap.php";
if (is_get()) {
    add_html_header();
    $redirect_back = "/";
    add_dynamic_template("login_form", "text/html");
    add_html_footer();
}

if (is_post()) {
    echo $_POST["foo"];
}