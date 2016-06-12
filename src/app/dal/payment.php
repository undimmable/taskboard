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
require_once "dal/dal_helper.php";
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

function payment_get_last_user_tx_id($user_id)
{
    $connection = get_account_connection();
    $stmt = mysqli_prepare($connection, "SELECT last_tx_id FROM db_account.account WHERE user_id=?");
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

function payment_fetch_transactions_after($tx_id)
{
    $connection = get_payment_connection();
    return mysqli_query($connection, "SELECT id,amount,processed,type FROM db_tx.tx WHERE id=$tx_id", MYSQLI_ASSOC);
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

function __payment_init_transaction($id_from, $id_to, $amount, $type)
{
    $db_errors = initialize_db_errors();
    $connection = get_payment_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "INSERT INTO db_tx.tx (id_from, id_to, amount, type) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'idsi', $id_from, $id_to, $amount, $type)) {
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

function payment_init_lock_transaction($id_from, $id_to, $amount)
{
    return __payment_init_transaction($id_from, $id_to, $amount, 'l');
}

function payment_init_pay_transaction($id_from, $id_to, $amount)
{
    return __payment_init_transaction($id_from, $id_to, $amount, 'p');
}

function payment_process_transaction($tx_id, $id_from, $amount = null)
{
    if ($amount == null) {
        $result = mysqli_query(get_payment_connection(), "SELECT amount from db_tx.tx WHERE id=$tx_id", MYSQLI_STORE_RESULT);
        if (!$result || mysqli_num_rows($result) < 1) {
            return false;
        }
        $amount_array = mysqli_fetch_array($result, MYSQLI_ASSOC);
        if (!$amount_array) {
            return false;
        } else {
            $amount = $amount_array[AMOUNT];
        }
    }
    if (payment_lock_balance($id_from, $tx_id, $amount)) {
        return __payment_transaction_set_processed($tx_id);
    } else {
        return false;
    }
}

function payment_get_unprocessed_transaction($entity_id_from, $entity_id_to, $type)
{
    $db_errors = initialize_db_errors();
    $connection = get_payment_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "SELECT id FROM db_tx.tx WHERE id_from=? AND id_to=? AND type=? AND NOT processed");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'iis', $entity_id_from, $entity_id_to, $type)) {
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

function __payment_transaction_set_processed($id)
{
    return mysqli_query(get_payment_connection(), "UPDATE db_tx.tx SET processed=TRUE WHERE id=$id");
}

function payment_fetch_balance($user_id)
{
    $db_errors = initialize_db_errors();
    $connection = get_account_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "SELECT balance-locked_balance FROM db_account.account WHERE user_id=?");
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