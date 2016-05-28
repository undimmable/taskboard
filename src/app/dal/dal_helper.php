<?php

require_once "../bootstrap.php";

define('ACCOUNT_DB', 'account');
define('LOGIN_DB', 'login');
define('TASK_DB', 'task');
define('TEXT_IDX_DB', 'text_idx');
define('TX_DB', 'tx');
define('USER_DB', 'user');
define('USER_INFO_DB', 'user_info');

function &initialize_db_errors()
{
    $db_errors = [];
    return $db_errors;
}

function get_db_errors()
{
    global $db_errors;
    return $db_errors;
}

function add_error($mysqli, &$db_errors)
{
    $error = mysqli_error($mysqli);
    array_push($db_errors, $error);
    error_log($error);
}

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