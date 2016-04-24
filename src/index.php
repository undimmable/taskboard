<?php
require_once "app/bootstrap.php";
if (!isAuthorized()) {
    https_redirect("/api/v1/auth/login", "/feed");
    die();
} else {
    https_redirect("/feed");
}