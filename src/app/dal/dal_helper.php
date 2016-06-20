<?php
/**
 * Dal functions
 *
 * PHP version 5
 *
 * @category  DalFunctions
 * @package   Dal
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */
define('ACCOUNT_DB', 'account');
define('EVENT_DB', 'event');
define('LOGIN_DB', 'login');
define('TASK_DB', 'task');
define('TEXT_IDX_DB', 'text_idx');
define('TX_DB', 'tx');
define('USER_DB', 'user');
define('MYSQL_LOG_STATEMENT', true);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$connections = [];
$connections[ACCOUNT_DB] = null;
$connections[EVENT_DB] = null;
$connections[LOGIN_DB] = null;
$connections[TASK_DB] = null;
$connections[TX_DB] = null;
$connections[USER_DB] = null;

function get_account_connection()
{
    if ($GLOBALS['connections'][ACCOUNT_DB] === null) {
        $GLOBALS['connections'][ACCOUNT_DB] = get_dal_connection(ACCOUNT_DB);
    }
    return $GLOBALS['connections'][ACCOUNT_DB];
}

function get_event_connection()
{
    if ($GLOBALS['connections'][EVENT_DB] === null) {
        $GLOBALS['connections'][EVENT_DB] = get_dal_connection(EVENT_DB);
    }
    return $GLOBALS['connections'][EVENT_DB];
}

function get_login_connection()
{
    if ($GLOBALS['connections'][LOGIN_DB] === null) {
        $GLOBALS['connections'][LOGIN_DB] = get_dal_connection(LOGIN_DB);
    }
    return $GLOBALS['connections'][LOGIN_DB];
}

function get_payment_connection()
{
    if ($GLOBALS['connections'][TX_DB] === null) {
        $GLOBALS['connections'][TX_DB] = get_dal_connection(TX_DB);
    }
    return $GLOBALS['connections'][TX_DB];
}

function get_task_connection()
{
    if ($GLOBALS['connections'][TASK_DB] === null) {
        $GLOBALS['connections'][TASK_DB] = get_dal_connection(TASK_DB);
    }
    return $GLOBALS['connections'][TASK_DB];
}

function get_text_idx_connection()
{
    if ($GLOBALS['connections'][TEXT_IDX_DB] === null) {
        $GLOBALS['connections'][TEXT_IDX_DB] = get_dal_connection(TEXT_IDX_DB);
    }
    return $GLOBALS['connections'][TEXT_IDX_DB];
}

function get_user_connection()
{
    if ($GLOBALS['connections'][USER_DB] === null) {
        $GLOBALS['connections'][USER_DB] = get_dal_connection(USER_DB);
    }
    return $GLOBALS['connections'][USER_DB];
}

function get_entity_db_name($entity_name)
{

    $db_config = get_db_config();
    $entity_db_config = $db_config[$entity_name];
    return $entity_db_config['database'];
}

function get_account_db_name()
{
    return get_entity_db_name(ACCOUNT_DB);
}

function get_event_db_name()
{
    return get_entity_db_name(EVENT_DB);
}

function get_login_db_name()
{
    return get_entity_db_name(LOGIN_DB);
}

function get_payment_db_name()
{
    return get_entity_db_name(TX_DB);
}

function get_task_db_name()
{
    return get_entity_db_name(TASK_DB);
}

function get_text_idx_db_name()
{
    return get_entity_db_name(TEXT_IDX_DB);
}

function get_user_db_name()
{
    return get_entity_db_name(USER_DB);
}

function close_account_connection()
{
    if ($GLOBALS['connections'][ACCOUNT_DB] !== null) {
        mysqli_close($GLOBALS['connections'][ACCOUNT_DB]);
        unset($GLOBALS['connections'][ACCOUNT_DB]);
    }
}

function close_event_connection()
{
    if ($GLOBALS['connections'][EVENT_DB] !== null) {
        mysqli_close($GLOBALS['connections'][EVENT_DB]);
        unset($GLOBALS['connections'][EVENT_DB]);
    }
}

function close_login_connection()
{
    if ($GLOBALS['connections'][LOGIN_DB] !== null) {
        mysqli_close($GLOBALS['connections'][LOGIN_DB]);
        unset($GLOBALS['connections'][LOGIN_DB]);
    }
}

function close_payment_connection()
{
    if ($GLOBALS['connections'][TX_DB] !== null) {
        mysqli_close($GLOBALS['connections'][TX_DB]);
        unset($GLOBALS['connections'][TX_DB]);
    }
}

function close_task_connection()
{
    if ($GLOBALS['connections'][TASK_DB] !== null) {
        mysqli_close($GLOBALS['connections'][TASK_DB]);
        unset($GLOBALS['connections'][TASK_DB]);
    }
}

function close_text_idx_connection()
{
    if ($GLOBALS['connections'][TEXT_IDX_DB] !== null) {
        mysqli_close($GLOBALS['connections'][TEXT_IDX_DB]);
        unset($GLOBALS['connections'][TEXT_IDX_DB]);
    }
}

function close_user_connection()
{
    if ($GLOBALS['connections'][USER_DB] !== null) {
        mysqli_close($GLOBALS['connections'][USER_DB]);
        unset($GLOBALS['connections'][USER_DB]);
    }
}

function &initialize_dal_errors()
{
    $GLOBALS['db_errors'] = [];
    return $GLOBALS['db_errors'];
}

function get_dal_errors()
{
    return $GLOBALS['db_errors'];
}

function add_dal_error($mysqli, &$db_errors)
{
    $error = mysqli_error($mysqli);
    array_push($db_errors, $error);
    error_log($error);
}

function get_dal_connection($entity_name)
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

function start_dal_transaction($connection)
{
    if (!mysqli_begin_transaction($connection)) {
        add_dal_error($connection, $GLOBALS['db_errors']);
        return false;
    } else {
        return true;
    }
}

function commit_dal_transaction($connection)
{
    if (!mysqli_commit($connection)) {
        add_dal_error($connection, $GLOBALS['db_errors']);
        return false;
    } else {
        return true;
    }
}

function rollback_dal_transaction($connection)
{
    if (!mysqli_rollback($connection)) {
        add_dal_error($connection, $GLOBALS['db_errors']);
        return false;
    } else {
        return true;
    }
}

