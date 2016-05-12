<?php
$validation_config = null;

function get_validation_config()
{
    global $php_config_path, $validation_config;
    if (is_null($validation_config)) {
        /** @noinspection PhpIncludeInspection */
        $validation_config = include "$php_config_path/taskboard_validation_config.php";
    }
    return $validation_config;
}

function get_config_min_password_length()
{
    return get_validation_config()['min_password_length'];
}

function get_config_max_email_length()
{
    return get_validation_config()['max_email_length'];
}

function get_config_max_amount()
{
    return get_validation_config()['max_task_amount'];
}