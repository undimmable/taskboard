<?php

function initialize_configuration($config_location = '/etc/php5/fpm/conf.d/db_config.ini')
{
    return parse_ini_file($config_location, true);
}

$db_config = initialize_configuration($config_location);