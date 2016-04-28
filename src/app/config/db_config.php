<?php
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