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

function fetch_balance($user_id)
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