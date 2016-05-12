<?php
if (!isset($php_config_path)) {
    $php_config_path = "/var/www/taskboard_config";
};
require_once "config/constants.php";
require_once "config/db_config.php";
require_once "config/security_config.php";
require_once "config/validation_config.php";
require_once "config/role.php";
require_once "lib/helper.php";
require_once "lib/validation.php";
require_once "lib/mobile.php";
require_once "lib/router.php";
require_once "security/force_https.php";
require_once "security/jwt.php";
require_once "security/token_auth.php";
require_once "view/helper.php";

try_authorize_from_cookie();