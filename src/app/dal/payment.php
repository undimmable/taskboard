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

function payment_fetch_transactions_after($tx_id, $user_id, $is_customer)
{
    $connection = get_payment_connection();
    $query = $is_customer ? "id_from=$user_id" : "id_to = $user_id";
    $mysqli_result = mysqli_query($connection, "SELECT id,id_from,amount,processed,type FROM db_tx.tx WHERE $query AND id>$tx_id", MYSQLI_ASSOC);
    $result = mysqli_fetch_array($mysqli_result);
    mysqli_free_result($mysqli_result);
    return $result;
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

function payment_pay($tx_id, $customer_id, $performer_id, $amount, $commission)
{
    $db_errors = initialize_db_errors();
    $connection = get_account_connection();
    mysqli_autocommit($connection, false);
    $result = mysqli_query($connection, "UPDATE db_account.account SET locked_balance = locked_balance - $amount - $commission, balance = balance - $amount - $commission WHERE user_id=$customer_id AND locked_balance - $amount - $commission > 0 AND balance - $amount - $commission > 0");
    if (!$result) {
        add_error($connection, $db_errors);
        mysqli_rollback($connection);
        mysqli_autocommit($connection, true);
        return false;
    }
    $result = mysqli_query($connection, "UPDATE db_account.account SET balance = balance + $amount, last_tx_id=$tx_id WHERE user_id=$performer_id AND (last_tx_id is NULL or last_tx_id < $tx_id)");
    if (!$result) {
        add_error($connection, $db_errors);
        mysqli_rollback($connection);
        mysqli_autocommit($connection, true);
        return false;
    }
    return mysqli_commit($connection);
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

/**
 * @param $task_id
 * @param $customer_id
 * @param $amount
 * @return bool|mysqli_result
 */
function payment_retry_lock_transaction($task_id, $customer_id, $amount)
{
    $lock_tx_id_processed = payment_get_transaction_by_participants($customer_id, $task_id, 'l');
    if (is_null($lock_tx_id_processed) || $lock_tx_id_processed[PROCESSED] === false) {
        $tx_id = is_null($lock_tx_id_processed) ? $lock_tx_id_processed[ID] : null;
        $tx_lock_processed = _lock($customer_id, $task_id, $amount, $tx_id);
        return $tx_lock_processed;
    } else {
        $tx_lock_processed = true;
        return $tx_lock_processed;
    }
}

function _lock($user_id, $task_id, $amount, $tx_id)
{
    if (is_null($tx_id)) {
        $tx_id = payment_init_lock_transaction($user_id, $task_id, $amount);
    }
    if (is_null($tx_id) || !$tx_id)
        return false;
    return payment_process_lock_transaction($tx_id, $user_id);
}

function _payment_init_transaction($id_from, $id_to, $amount, $type)
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
    if (!mysqli_stmt_bind_param($stmt, 'idss', $id_from, $id_to, $amount, $type)) {
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
    return _payment_init_transaction($id_from, $id_to, $amount, 'l');
}

function payment_init_pay_transaction($task_id, $performer_id, $amount)
{
    return _payment_init_transaction($task_id, $performer_id, $amount, 'p');
}

function payment_process_lock_transaction($tx_id, $id_from, $amount = null)
{
    if ($amount == null) {
        $connection = get_payment_connection();
        $result = mysqli_query($connection, "SELECT amount from db_tx.tx WHERE id=$tx_id", MYSQLI_STORE_RESULT);
        if (!$result || mysqli_num_rows($result) < 1) {
            return false;
        }
        $amount_array = mysqli_fetch_array($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
        if (!$amount_array) {
            return false;
        } else {
            $amount = $amount_array[AMOUNT];
        }
    }
    if (payment_lock_balance($id_from, $tx_id, $amount)) {
        return _payment_transaction_set_processed($tx_id);
    } else {
        return false;
    }
}

function payment_process_pay_transaction($tx_id, $customer_id = null, $performer_id = null, $amount = null, $commission = null)
{
    if (is_null($amount) || is_null($commission)) {
        $payment_connection = get_payment_connection();
        $result = mysqli_query($payment_connection, "SELECT id_from FROM db_tx.tx WHERE id=$tx_id");
        if (!$result) {
            mysqli_free_result($result);
            return false;
        }
        $task_id = mysqli_fetch_array($result, MYSQLI_ASSOC)['id_from'];
        mysqli_free_result($result);
        $task = dal_task_fetch($task_id);
        if ($task)
            return false;
        $amount = $task[PRICE];
        $commission = $task[COMMISSION];
    }
    $result = mysqli_query(get_payment_connection(), "SELECT processed FROM db_tx.tx WHERE id=$tx_id");
    if (mysqli_num_rows($result) < 1) {
        if (payment_pay($tx_id, $customer_id, $performer_id, $amount, $commission)) {
            return _payment_transaction_set_processed($tx_id);
        } else {
            return false;
        }
    }
    if (!$result) {
        mysqli_free_result($result);
        return false;
    }
    $processed = mysqli_fetch_array($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
    if (is_null($processed)) {
        return false;
    } else if (!$processed[PROCESSED]) {
        if (payment_pay($tx_id, $customer_id, $performer_id, $amount, $commission)) {
            return _payment_transaction_set_processed($tx_id);
        } else {
            return false;
        }
    } else {
        return _payment_transaction_set_processed($tx_id);
    }
}

function payment_get_transaction_by_participants($entity_id_from, $entity_id_to, $type)
{
    $db_errors = initialize_db_errors();
    $connection = get_payment_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "SELECT id, processed FROM db_tx.tx WHERE id_from=? AND id_to=? AND type=?");
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
    if (!mysqli_stmt_bind_result($stmt, $id, $processed)) {
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
    return [
        ID => $id,
        PROCESSED => $processed
    ];
}

function _payment_transaction_set_processed($id)
{
    $connection = get_payment_connection();
    $mysqli_result = mysqli_query($connection, "UPDATE db_tx.tx SET processed=TRUE WHERE id=$id");
    $success = false;
    if ($mysqli_result)
        $success = true;
    return $success;
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
