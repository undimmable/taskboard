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
require_once "../bootstrap.php";
require_once "dal_helper.php";

function dal_create_user($email, $role, $hashed_password, $confirmation_token)
{
    if (dal_user_exists($email)) {
        $GLOBALS['db_errors'][LOGIN] = "duplicate entity";
        return false;
    }
    initialize_dal_errors();
    $connection = get_user_connection();
    if (!$connection)
        add_dal_error($connection, $db_errors);
    $stmt = mysqli_prepare($connection, "INSERT INTO db_user.user (email, role, hashed_password, confirmation_token) VALUES (?,?,?,?)");
    if (!$stmt) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'siss', $email, $role, $hashed_password, hash('md5', $confirmation_token))) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_close($stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (mysqli_errno($connection) !== 0) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    return [EMAIL => $email, ROLE => $role, ID => mysqli_insert_id($connection)];
}

function dal_user_exists($email)
{
    initialize_dal_errors();
    $connection = get_user_connection();
    if (!$connection)
        add_dal_error($connection, $db_errors);
    $mysqli_stmt = mysqli_prepare($connection, "SELECT count(*) AS count FROM db_user.user WHERE email = ?");
    if (!$mysqli_stmt) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_param($mysqli_stmt, 's', $email)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($mysqli_stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_result($mysqli_stmt, $count)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_fetch($mysqli_stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_close($mysqli_stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    return $count > 0;
}

function dal_now() {
    $db_errors = initialize_dal_errors();
    $connection = get_event_connection();
    if (!$connection) {
        add_dal_error($connection, $db_errors);
        return 0;
    }
    $mysqli_result = mysqli_query($connection, "SELECT UNIX_TIMESTAMP() as ts;");
    $result = mysqli_fetch_array($mysqli_result, MYSQLI_ASSOC);
    mysqli_free_result($mysqli_result);
    return $result['ts'];
}

function dal_user_update_password_by_email($email, $hashed_password, $ts)
{
    initialize_dal_errors();
    $connection = get_user_connection();
    if (!$connection)
        add_dal_error($connection, $db_errors);
    $mysqli_stmt = mysqli_prepare($connection, "UPDATE db_user.user SET hashed_password=? WHERE email=? AND UNIX_TIMESTAMP(DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 15 minute)) < ?");
    if (!$mysqli_stmt) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($mysqli_stmt, 'ssi', $hashed_password, $email, $ts)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($mysqli_stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (mysqli_affected_rows($connection) < 1) {
        return false;
    }
    if (!mysqli_stmt_close($mysqli_stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    return true;
}

function dal_fetch_user_by_email($email)
{
    initialize_dal_errors();
    $connection = get_user_connection();
    if (!$connection)
        add_dal_error($connection, $db_errors);
    $stmt = mysqli_prepare($connection, "SELECT id, email, hashed_password, role FROM db_user.user WHERE email = ?");
    if (!$stmt) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_param($stmt, 's', $email)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_result($stmt, $id, $user_email, $hashed_password, $role)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    $fetched = mysqli_stmt_fetch($stmt);
    if (is_null($fetched))
        return null;
    else if (!$fetched) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_close($stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    return [
        ID => $id,
        EMAIL => $user_email,
        HASHED_PASSWORD => $hashed_password,
        ROLE => $role
    ];
}

function dal_fetch_user($id)
{
    initialize_dal_errors();
    $connection = get_user_connection();
    if (!$connection)
        add_dal_error($connection, $db_errors);
    $mysqli_stmt = mysqli_prepare($connection, "SELECT id, email, hashed_password, role FROM db_user.user WHERE id = ?");
    if (!$mysqli_stmt) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_param($mysqli_stmt, 'i', $id)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($mysqli_stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_result($mysqli_stmt, $id, $user_id, $user_email, $hashed_password, $role)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_fetch($mysqli_stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_close($mysqli_stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    return [
        ID => $id,
        EMAIL => $user_email,
        HASHED_PASSWORD => $hashed_password,
        ROLE => $role
    ];
}

function dal_verify_user($confirmation_token)
{
    initialize_dal_errors();
    $connection = get_user_connection();
    if (!$connection)
        add_dal_error($connection, $db_errors);
    $mysqli_stmt = mysqli_prepare($connection, "UPDATE db_user.user SET confirmed=TRUE WHERE NOT confirmed AND confirmation_token = ? AND LAST_INSERT_ID(id) OR LAST_INSERT_ID(0)");
    if (!$mysqli_stmt) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_param($mysqli_stmt, 's', hash('md5', $confirmation_token))) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($mysqli_stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (mysqli_affected_rows($connection) < 1) {
        return false;
    }
    if (!mysqli_stmt_close($mysqli_stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    $id = mysqli_stmt_insert_id($mysqli_stmt);
    if ($id < 1) {
        return false;
    }
    return dal_fetch_user($id);
}

