<?php
/**
 * Security functions
 *
 * PHP version 5
 *
 * @category  SecurityFunctions
 * @package   Security
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */
if (isset($_SERVER['REQUEST_URI'])) {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubdomains;');
    } else {
        $uri = $_SERVER['REQUEST_URI'];
        https_redirect($uri);
        die();
    }
}
