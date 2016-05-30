<?php
/**
 * @author dimyriy
 * @version 1.0
 */

require_once "../bootstrap.php";
require_once "dal_helper.php";
$payment_connection = null;
$account_connection = null;

function get_payment_connection()
{
    global $payment_connection;
    if ($payment_connection === null) {
        $payment_connection = get_mysqli_connection(TX_DB);
    }
    return $payment_connection;
}

function get_account_connection()
{
    global $account_connection;
    if ($account_connection === null) {
        $account_connection = get_mysqli_connection(ACCOUNT_DB);
    }
    return $account_connection;
}

function payment_check_able_to_process($user_id, $amount)
{
    $balance = payment_fetch_balance($user_id);
    if (!$balance)
        return false;
    return $balance - $amount > 0;

}

function payment_lock_balance($user_id, $tx_id, $amount)
{
    $connection = get_account_connection();
    $stmt = mysqli_prepare($connection, "UPDATE db_account.account SET locked_balance = locked_balance + $amount, last_tx_id=? WHERE user_id=? AND $amount < account.balance - account.locked_balance");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'ii', $tx_id, $user_id)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (mysqli_stmt_affected_rows($stmt) != 1) {
        mysqli_stmt_close($stmt);
        return null;
    }
    mysqli_stmt_close($stmt);
    return true;
}

function payment_unlock_balance($user_id, $amount)
{
    $connection = get_account_connection();
    $stmt = mysqli_prepare($connection, "UPDATE db_account.account SET locked_balance = locked_balance - $amount WHERE user_id=?");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'i', $user_id)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (mysqli_stmt_affected_rows($stmt) != 1) {
        mysqli_stmt_close($stmt);
        return null;
    }
    mysqli_stmt_close($stmt);
    return true;
}

function payment_refill_balance($user_id, $amount)
{
    $connection = get_account_connection();
    $stmt = mysqli_prepare($connection, "UPDATE db_account.account SET balance = balance + $amount WHERE user_id=?");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'i', $user_id)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (mysqli_stmt_affected_rows($stmt) != 1) {
        mysqli_stmt_close($stmt);
        return null;
    }
    mysqli_stmt_close($stmt);
    return true;
}

function payment_create_account($user_id, $balance = DEFAULT_BALANCE)
{
    $db_errors = initialize_db_errors();
    $connection = get_account_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "INSERT INTO db_account.account (user_id, balance, last_tx_id, locked_balance) VALUES (?, ?, -1, 0.00)");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'id', $user_id, $balance)) {
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

function payment_create_transaction($user_from, $user_to, $amount, $type)
{
    $db_errors = initialize_db_errors();
    $connection = get_payment_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "INSERT INTO db_tx.tx (from_user_id, to_user_id, amount, type) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'idsi', $user_from, $user_to, $amount, $type)) {
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

function payment_process_transaction($from_user_id, $to_user_id, $amount)
{
    $connection = get_payment_connection();
    $stmt = mysqli_prepare($connection, "UPDATE db_account.account SET locked_balance = locked_balance + $amount WHERE user_id=? AND $amount < account.balance - account.locked_balance");
}

function payment_fetch_balance($user_id)
{
    $db_errors = initialize_db_errors();
    $connection = get_account_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "SELECT balance FROM db_account.account WHERE user_id=?");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_param($stmt, 'i', $user_id)) {
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
        mysqli_stmt_close($stmt);
        return null;
    }
    if (!mysqli_stmt_bind_result($stmt, $balance)) {
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
    return $balance;
}