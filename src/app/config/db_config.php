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
    if (is_null($GLOBALS['db_config'])) {
        /** @noinspection PhpIncludeInspection */
        $GLOBALS['db_config'] = include "taskboard_db_config.php";
    }
}

function get_db_config()
{
    return $GLOBALS['db_config'];
}

initialize_configuration();
