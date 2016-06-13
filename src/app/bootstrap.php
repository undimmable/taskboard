<?php
/**
 * Application Bootstrap
 *
 * PHP version 5
 *
 * @category  ApplicationBootstrap
 * @package   Root
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/taskboard/src/app');
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/taskboard_config');
$GLOBALS['staging'] = getenv('TESTS_STAGING');
if($GLOBALS['staging'] == "")
    $GLOBALS['staging'] = false;
require_once "config/constants.php";
require_once "config/db_config.php";
require_once "config/security_config.php";
require_once "config/validation_config.php";
require_once "config/role.php";
require_once "events/event.php";
require_once "lib/helper.php";
require_once "lib/validation.php";
require_once "lib/router.php";
require_once "security/force_https.php";
require_once "security/jwt.php";
require_once "security/token_auth.php";
require_once "view/view_helper.php";
