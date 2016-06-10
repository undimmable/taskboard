<?php
/**
 * Database configuration functions
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

$db_config = null;
function initialize_configuration()
{
    global $php_config_path, $db_config;
    if (is_null($db_config)) {
        /** @noinspection PhpIncludeInspection */
        $db_config = include "$php_config_path/taskboard_db_config.php";
    }
}

function get_db_config()
{
    global $db_config;
    return $db_config;
}

initialize_configuration();