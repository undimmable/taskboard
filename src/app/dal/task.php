<?php
/**
 * @author dimyriy
 * @version 1.0
 */
require_once "../bootstrap.php";
require_once "dal_helper.php";
$task_connection = null;

function get_task_connection()
{
    global $task_connection;
    if ($task_connection === null) {
        $task_connection = get_mysqli_connection(TASK_DB);
    }
    return $task_connection;
}

function dal_task_update_set_lock_tx_id($task_id, $tx_id)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "UPDATE db_task.task SET lock_tx_id = ? WHERE id = ? AND lock_tx_id = -1");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'ii', $tx_id, $task_id)) {
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

function dal_task_delete($task_id)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "UPDATE db_task.task SET deleted=TRUE WHERE id=?");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'i', $task_id)) {
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

function dal_task_create($customer_id, $amount, $description)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "INSERT INTO db_task.task (customer_id, amount, description, commission) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'idsi', $customer_id, $amount, $description, get_system_commission())) {
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

function dal_task_fetch($task_id)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "SELECT id, timestampdiff(SECOND, now(), created_at), customer_id, performer_id, amount, description, lock_tx_id FROM db_task.task WHERE id=?");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'i', $task_id)) {
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
    if (!mysqli_stmt_bind_result($stmt, $id, $created_at, $customer_id, $performer_id, $amount, $description, $lock_tx_id)) {
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
        DESCRIPTION => $description,
        CUSTOMER_ID => $customer_id,
        PERFORMER_ID => $performer_id,
        CREATED_AT_OFFSET => $created_at,
        LOCK_TX_ID => $lock_tx_id,
        AMOUNT => $amount
    ];
}

function dal_task_get_last_id($user_id)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "SELECT max(id) FROM db_task.task WHERE customer_id=?");
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
    if (!mysqli_stmt_bind_result($stmt, $id)) {
        add_error($connection, $db_errors);
        return false;
    }
    mysqli_stmt_fetch($stmt);
    if (!mysqli_stmt_close($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    return $id;
}

function dal_task_fetch_tasks_less_than_last_id_limit($callback, $user_id, $lock_tx_id_clause, $select_user_type, $limit = 100, $last_id = null)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $last_id_clause = $last_id === null ? '' : "AND id < $last_id";

    $query = "SELECT id, timestampdiff(SECOND, now(), created_at), customer_id, performer_id, amount, description, lock_tx_id FROM db_task.task WHERE $lock_tx_id_clause AND not deleted AND $select_user_type <=> ? $last_id_clause ORDER BY id DESC LIMIT ?";
    $stmt = mysqli_prepare($connection, $query);
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'ii', $user_id, $limit)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_result($stmt, $id, $created_at, $customer_id, $performer_id, $amount, $description, $lock_tx_id)) {
        add_error($connection, $db_errors);
        return false;
    }
    $row_number = 0;
    while ($row = mysqli_stmt_fetch($stmt)) {
        $row_number++;
        $task = [
            ID => $id,
            CREATED_AT_OFFSET => $created_at,
            CUSTOMER_ID => $customer_id,
            PERFORMER_ID => $performer_id,
            AMOUNT => $amount,
            DESCRIPTION => $description,
            'lock_tx_id' => $lock_tx_id
        ];
        call_user_func($callback, $task);
    }
    if ($row_number == 0) {
        return null;
    }
    if (!mysqli_stmt_close($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    return true;
}