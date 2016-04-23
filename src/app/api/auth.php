<?php
/**
 * Login functions
 *
 * PHP version 5
 *
 * @category  APIFunctions
 * @package   Api
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */
/**
 * Bootstrap application
 */
require_once "../bootstrap.php";
if ($_REQUEST["method"] === "login") {
    if (is_get()) {
        add_html_header();
        $redirect_back = "/";
        add_template("login_form", "text/html");
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
    render_not_found();
}
