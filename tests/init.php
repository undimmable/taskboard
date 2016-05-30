<?php
ini_set('date.timezone', 'UTC');
set_include_path(get_include_path() . PATH_SEPARATOR . 'src/app');
set_include_path(get_include_path() . PATH_SEPARATOR . 'vendor/');
set_include_path(get_include_path() . PATH_SEPARATOR . 'tests/integration/base');
/** @noinspection PhpIncludeInspection */
require_once 'autoload.php';
$php_config_path = 'tests/config';