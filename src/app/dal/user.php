<?php

require_once "../bootstrap.php";
require_once "dal_helper.php";

$user_connection = null;

function get_user_connection()
{
    global $user_connection;
    if ($user_connection === null) {
        $user_connection = get_mysqli_connection(USER_DB);
    }
    return $user_connection;
}

function create_user($email, $role, $hashed_password, $confirmation_token)
{
    if (user_exists($email)) {
        global $db_errors;
        $db_errors[LOGIN] = "duplicate entity";
        return false;
    }
    initialize_db_errors();
    $connection = get_mysqli_connection(USER_DB);
    if (!$connection)
        add_error(mysqli_error($connection), $db_errors);
    $stmt = mysqli_prepare($connection, "INSERT INTO db_user.user (email, role, hashed_password, confirmation_token) VALUES (?,?,?,?)");
    if (!$stmt) {
        add_error(mysqli_error($connection), $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'siss', $email, $role, $hashed_password, $confirmation_token)) {
        add_error(mysqli_error($connection), $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($stmt)) {
        add_error(mysqli_error($connection), $db_errors);
        return false;
    }
    if (!mysqli_stmt_close($stmt)) {
        add_error(mysqli_error($connection), $db_errors);
        return false;
    }
    if (mysqli_errno($connection) !== 0) {
        add_error(mysqli_error($connection), $db_errors);
        return false;
    }
    return [EMAIL => $email, ROLE => $role, ID => mysqli_insert_id($connection)];
}

function user_exists($email)
{
    initialize_db_errors();
    $mysqli = get_mysqli_connection(USER_DB);
    if (!$mysqli)
        add_error(mysqli_error($mysqli), $db_errors);
    $mysqli_stmt = mysqli_prepare($mysqli, "SELECT count(*) AS count FROM db_user.user WHERE email = ?");
    if (!$mysqli_stmt) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_param($mysqli_stmt, 's', $email)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($mysqli_stmt)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_result($mysqli_stmt, $count)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_fetch($mysqli_stmt)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_close($mysqli_stmt)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    return $count > 0;
}

function db_fetch_user_by_email($email)
{
    initialize_db_errors();
    $mysqli = get_mysqli_connection(USER_DB);
    if (!$mysqli)
        add_error(mysqli_error($mysqli), $db_errors);
    $stmt = mysqli_prepare($mysqli, "SELECT id, email, hashed_password, role FROM db_user.user WHERE email = ?");
    if (!$stmt) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_param($stmt, 's', $email)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($stmt)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_result($stmt, $id, $user_email, $hashed_password, $role)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_fetch($stmt)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (mysqli_stmt_num_rows($stmt) < 1) {
        return null;
    }
    if (!mysqli_stmt_close($stmt)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    return [
        ID => $id,
        EMAIL => $user_email,
        HASHED_PASSWORD => $hashed_password,
        ROLE => $role
    ];
}

function get_user_by_id($id)
{
    initialize_db_errors();
    $mysqli = get_mysqli_connection(USER_DB);
    if (!$mysqli)
        add_error(mysqli_error($mysqli), $db_errors);
    $mysqli_stmt = mysqli_prepare($mysqli, "SELECT id, email, hashed_password, role FROM db_user.user WHERE id = ?");
    if (!$mysqli_stmt) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_param($mysqli_stmt, 'i', $id)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($mysqli_stmt)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_result($mysqli_stmt, $id, $user_id, $user_email, $hashed_password, $role)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_fetch($mysqli_stmt)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_close($mysqli_stmt)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    return [
        ID => $id,
        EMAIL => $user_email,
        HASHED_PASSWORD => $hashed_password,
        ROLE => $role
    ];
}

function verify_user($confirmation_token)
{
    initialize_db_errors();
    $mysqli = get_mysqli_connection(USER_DB);
    if (!$mysqli)
        add_error(mysqli_error($mysqli), $db_errors);
    $mysqli_stmt = mysqli_prepare($mysqli, "UPDATE db_user.user SET confirmed=TRUE WHERE NOT confirmed AND confirmation_token = ? AND LAST_INSERT_ID(id) OR LAST_INSERT_ID(0)");
    if (!$mysqli_stmt) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_param($mysqli_stmt, 's', $confirmation_token)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($mysqli_stmt)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    if (mysqli_affected_rows($mysqli) < 1) {
        return false;
    }
    if (!mysqli_stmt_close($mysqli_stmt)) {
        add_error(mysqli_error($mysqli), $db_errors);
        return false;
    }
    $id = mysqli_stmt_insert_id($mysqli_stmt);
    if ($id < 1) {
        return false;
    }
    return get_user_by_id($id);
}

