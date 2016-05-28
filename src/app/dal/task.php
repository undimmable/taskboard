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

function dal_task_create($customer_id, $amount, $description)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    mysqli_autocommit($connection, false);
    $task_tx_started = mysqli_begin_transaction($connection);
    if (!$task_tx_started) {
        add_error($connection, $db_errors);
        return false;
    }

    $stmt = mysqli_prepare($connection, "INSERT INTO db_task.task (customer_id, amount, description) VALUES (?, ?, ?)");
    if (!$stmt) {
        add_error($connection, $db_errors);
        mysqli_rollback($connection);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'ids', $customer_id, $amount, $description)) {
        add_error($connection, $db_errors);
        mysqli_rollback($connection);
        return false;
    }
    if (!mysqli_stmt_execute($stmt)) {
        add_error($connection, $db_errors);
        mysqli_rollback($connection);
        return false;
    }
    if (!mysqli_stmt_close($stmt)) {
        add_error($connection, $db_errors);
        mysqli_rollback($connection);
        return false;
    }
    if (mysqli_errno($connection) !== 0) {
        mysqli_rollback($connection);
        return false;
    }
    $task_id = mysqli_insert_id($connection);
    if (!mysqli_commit($connection)) {
        return false;
    }
    return $task_id;
}

function dal_task_fetch($task_id)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "SELECT id, timestampdiff(SECOND, now(), created_at), customer_id, performer_id, amount, description FROM db_task.task WHERE id=?");
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
    if (!mysqli_stmt_bind_result($stmt, $id, $created_at, $customer_id, $performer_id, $amount, $description)) {
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
        AMOUNT => $amount
    ];
}

function dal_task_fetch_all_tasks($callback, $user_id, $select_user_type, $limit = 100, $last_id = null)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $last_id_clause = $last_id === null ? '' : "AND id < $last_id";

    $query = "SELECT id, timestampdiff(SECOND, now(), created_at), customer_id, performer_id, amount, description FROM db_task.task WHERE $select_user_type <=> ? $last_id_clause ORDER BY id DESC LIMIT ?";
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
    if (!mysqli_stmt_bind_result($stmt, $id, $created_at, $customer_id, $performer_id, $amount, $description)) {
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
            DESCRIPTION => $description
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