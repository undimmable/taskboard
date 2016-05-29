<?php

define('ACCOUNT_DB', 'account');
define('LOGIN_DB', 'login');
define('TASK_DB', 'task');
define('TEXT_IDX_DB', 'text_idx');
define('TX_DB', 'tx');
define('USER_DB', 'user');
define('USER_INFO_DB', 'user_info');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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

function start_db_transaction($connection, &$db_errors)
{
    if (!mysqli_begin_transaction($connection)) {
        add_error($connection, $db_errors);
        return false;
    } else {
        return true;
    }
}

function commit_db_transaction($connection, &$db_errors)
{
    if (!mysqli_commit($connection)) {
        add_error($connection, $db_errors);
        return false;
    } else {
        return true;
    }
}

function rollback_db_transaction($connection)
{
    if (!mysqli_rollback($connection)) {
        add_error($connection, $db_errors);
        return false;
    } else {
        return true;
    }
}

function __build_values_clause($param_values)
{
    if (is_null($param_values))
        return '';
    $values_number = count($param_values);
    if ($values_number > 0) {
        $values_clause = "VALUES(?";
        for ($i = 1; $i < $values_number; $i++) {
            $values_clause = "$values_clause,?";
        }
        $values_clause = "$values_clause)";
        return $values_clause;
    } else {
        return '';
    }
}