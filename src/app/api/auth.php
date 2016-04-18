<?php
require_once "../bootstrap.php";
if ($_REQUEST["method"] === "login") {
    if (is_get()) {
        add_html_header();
        $redirect_back = "/";
        add_dynamic_template("login_form", "text/html");
        add_html_footer();
    }

    if (is_post()) {
        $token = ["username" => $_POST["username"]];
        setToken($token);
        https_redirect($_POST["back"]);
    }
} elseif ($_REQUEST["method"] === "logout") {
    if (deleteToken()) {
        http_response_code(200);
        https_redirect("/");
    } else {
        http_response_code(401);
    }
} else {
    http_response_code(404);
}