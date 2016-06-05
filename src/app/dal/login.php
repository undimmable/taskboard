<?php

require_once "bootstrap.php";
require_once "dal_helper.php";

$login_connection = null;

function get_login_connection()
{
    global $login_connection;
    if ($login_connection === null) {
        $login_connection = get_mysqli_connection(LOGIN_DB);
    }
    return $login_connection;
}


function dal_login_create_or_update($user_id, $ip, $client)
{
    $db_errors = initialize_db_errors();
    $connection = get_login_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "INSERT INTO db_login.login (user_id, ip, user_client) VALUES (?,?,?) ON DUPLICATE KEY UPDATE user_id=?, failed_attepts = 0, last_login=now()");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'issi', $user_id, $ip, $client, $user_id)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_close($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (mysqli_errno($connection) !== 0) {
        return false;
    }
    return true;
}

function dal_login_fetch($user_id, $ip, $client)
{
    $db_errors = initialize_db_errors();
    $connection = get_login_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "SELECT id FROM db_login.login WHERE user_id=? AND ip=? AND user_client=?");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'iss', $user_id, $ip, $client)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_store_result($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (mysqli_stmt_num_rows($stmt) < 1) {
        return null;
    }
    if (!mysqli_stmt_bind_result($stmt, $id)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_fetch($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_close($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (mysqli_errno($connection) !== 0) {
        return false;
    }
    return $id;
}

function dal_login_log_failed($ip, $client)
{
    $db_errors = initialize_db_errors();
    $connection = get_login_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "INSERT INTO db_login.login (ip, user_client, failed_attepts) VALUES (?,?,1) ON DUPLICATE KEY UPDATE failed_attepts = failed_attepts + 1, last_login=now()");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'ss', $ip, $client)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_close($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (mysqli_errno($connection) !== 0) {
        return false;
    }
    $id = mysqli_insert_id($connection);
    return $id;
}

function dal_login_being_failed($ip, $client, $max_attempts, $interval_in_seconds)
{
    initialize_db_errors();
    $connection = get_login_connection();
    if (!$connection)
        add_error($connection, $db_errors);
    $stmt = mysqli_prepare($connection, "SELECT failed_attepts FROM db_login.login WHERE ip = ? AND user_client = ? AND failed_attepts >= ? AND last_login > timestamp(DATE_SUB(NOW(), INTERVAL ? SECOND))");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'ssii', $ip, $client, $max_attempts, $interval_in_seconds)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_result($stmt, $failed_attempts)) {
        add_error($connection, $db_errors);
        return false;
    }
    $fetched = mysqli_stmt_fetch($stmt);
    if (is_null($fetched))
        return null;
    else if (!$fetched) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_close($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    return $failed_attempts;
}