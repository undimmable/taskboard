<?php
/**
 * Validation configuration functions
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

$validation_config = null;

function get_validation_config()
{
    global $php_config_path, $validation_config;
    if (is_null($validation_config)) {
        /** @noinspection PhpIncludeInspection */
        $validation_config = include "taskboard_validation_config.php";
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

function get_config_min_amount()
{
    return get_validation_config()['min_task_amount'];
}

function get_config_max_task_description_length()
{
    return get_validation_config()['max_task_description_length'];
}

function get_config_max_task_selection_limit()
{
    return get_validation_config()['max_task_selection_limit'];
}

function get_config_failed_attempt_timeout()
{
    return get_validation_config()['failed_attempt_timeout'];
}

function get_config_failed_attempt_retry()
{
    return get_validation_config()['failed_attempt_retry'];
}