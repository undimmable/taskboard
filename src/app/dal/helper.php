<?php

require_once "../bootstrap.php";

function get_mysqli_connection($entity_name)
{
    $db_config = get_db_config();
    $entity_db_config = $db_config[$entity_name];
    $host = $entity_db_config['host'];
    $port = $entity_db_config['port'];
    $user = $entity_db_config['user'];
    $password = $entity_db_config['password'];
    $database = $entity_db_config['database'];
    return mysqli_connect($host, $user, $password, $database, $port);
}