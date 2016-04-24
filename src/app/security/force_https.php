<?php
if (isset($_SERVER['REQUEST_URI'])) {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubdomains;');
    } else {
        $uri = $_SERVER['REQUEST_URI'];
        https_redirect($uri);
        die();
    }
}
