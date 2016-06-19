<?php
/**
 * Security configuration functions
 *
 * PHP version 5
 *
 * @category  ConfigFunctions
 * @package   Config
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */

$security_config = null;

function get_security_config()
{
    if (is_null($GLOBALS['security_config'])) {
        /** @noinspection PhpIncludeInspection */
        $security_config = include "taskboard_security_config.php";
    }
    return $GLOBALS['security_config'];
}

function get_config_jwt_secret()
{
    return get_security_config()['jwt_secret'];
}

function get_config_confirmation_secret()
{
    return get_security_config()['confirmation_secret'];
}

function get_config_task_csrf_secret()
{
    return get_security_config()['task_csrf_secret'];
}

function get_config_login_csrf_secret()
{
    return get_security_config()['login_csrf_secret'];
}

function get_config_account_csrf_secret()
{
    return get_security_config()['account_csrf_secret'];
}

function get_config_payload_secret()
{
    return get_security_config()['payload_secret'];
}

function get_config_events_secret()
{
    return get_security_config()['events_secret'];
}
