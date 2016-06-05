<?php
if (!isset($php_config_path)) {
    $php_config_path = "/var/www/taskboard_config";
};
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/taskboard/src/app');
require_once "config/constants.php";
require_once "config/db_config.php";
require_once "config/security_config.php";
require_once "config/validation_config.php";
require_once "config/role.php";
require_once "events/event.php";
require_once "lib/helper.php";
require_once "lib/validation.php";
require_once "lib/mobile.php";
require_once "lib/router.php";
require_once "security/force_https.php";
require_once "security/jwt.php";
require_once "security/token_auth.php";
require_once "view/view_helper.php";