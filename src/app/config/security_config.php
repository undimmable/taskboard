<?php
$security_config = null;

function get_security_config()
{
    global $php_config_path, $security_config;
    if (is_null($security_config)) {
        /** @noinspection PhpIncludeInspection */
        $security_config = include "$php_config_path/taskboard_security_config.php";
    }
    return $security_config;
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

function get_config_vk_client_id()
{
    return get_security_config()['vk_client_id'];
}

function get_config_vk_secret()
{
    return get_security_config()['vk_secret'];
}