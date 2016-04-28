<?php
$key_config = null;

function get_key_config()
{
    global $php_config_path, $key_config;
    if (is_null($key_config)) {
        /** @noinspection PhpIncludeInspection */
        $key_config = include "$php_config_path/taskboard_key_config.php";
    }
    return $key_config;
}

function get_key_jwt_secret()
{
    return get_key_config()['jwt_secret'];
}

function get_key_confirmation_secret()
{
    return get_key_config()['confirmation_secret'];
}

function get_key_vk_client_id()
{
    return get_key_config()['vk_client_id'];
}           

function get_key_vk_secret()
{
    return get_key_config()['vk_secret'];
}

function get_minimal_password_length()
{
    return get_key_config()['minimal_password_length'];
}