<?php

require_once "../bootstrap.php";
require_once "dal_helper.php";
$text_idx_connection = null;

function get_text_idx_connection()
{
    global $text_idx_connection;
    if ($text_idx_connection === null) {
        $text_idx_connection = get_mysqli_connection(TEXT_IDX_DB);
    }
    return $text_idx_connection;
}

function find_object($text)
{
    $db_errors = initialize_db_errors();
    $connection = get_text_idx_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "SELECT entity_id, entity_type, text_val FROM db_text_idx.text_idx WHERE MATCH(text_val) AGAINST (? IN NATURAL LANGUAGE MODE)");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_param($stmt, 's', $text)) {
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
    if (!mysqli_stmt_bind_result($stmt, $entity_type, $entity_id, $text_val)) {
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
        "entity_type" => $entity_type,
        "entity_id" => $entity_type,
        "text_val" => $text_val
    ];
}

function add_object($entity_id, $entity_type, $text)
{
    $db_errors = initialize_db_errors();
    $connection = get_text_idx_connection();
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

    $stmt = mysqli_prepare($connection, "INSERT INTO db_text_idx.text_idx (entity_id, entity_type, text_val) VALUES (?, ?, ?)");
    if (!$stmt) {
        add_error($connection, $db_errors);
        mysqli_rollback($connection);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'iss', $entity_id, $entity_type, $text)) {
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
    $id = mysqli_insert_id($connection);
    if (!mysqli_commit($connection)) {
        return false;
    }
    return $id;
}