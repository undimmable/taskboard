<?php
require_once "app/bootstrap.php";
$page_title = "index";
if (!is_authorized()) {
    add_html_header();
    add_html_navbar();
    add_html_footer();
    die();
} else {
    https_redirect("/feed");
}