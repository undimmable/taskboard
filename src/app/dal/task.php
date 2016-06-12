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
/**
 * Require application bootstrap and dal_helper
 */
require_once "../bootstrap.php";
require_once "dal_helper.php";
$task_connection = null;

/**
 * Helper function returning cached task connection
 *
 * @return mysqli
 */
function get_task_connection()
{
    global $task_connection;
    if ($task_connection === null) {
        $task_connection = get_mysqli_connection(TASK_DB);
    }
    return $task_connection;
}

/**
 * Set transaction id to task
 *
 * @param $task_id
 * @param $tx_id
 * @return bool|null null if the row with specified task_id doesn't exists, true if update was successful and false if there was some errors
 */
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

/**
 * Delete the task with specified id
 *
 * @param $task_id
 * @return bool|null null if the row with specified task_id doesn't exists, true if update was successful and false if there was some errors
 */
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

/**
 * Create task for customer_id with given amount and description
 *
 * @param $customer_id
 * @param $amount
 * @param $description
 * @return bool|int|string  null if the row with specified task_id doesn't exists, task_id if creation was successful and false if there was some errors
 */
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

/**
 * Fetch task by id
 *
 * @param $task_id
 * @return array|bool  task object represented as assoc array was successful and false if there was some errors
 */
function dal_task_fetch($task_id)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "SELECT id, timestampdiff(SECOND, now(), created_at), customer_id, performer_id, amount, description, paid FROM db_task.task WHERE id=?");
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
    if (!mysqli_stmt_bind_result($stmt, $id, $created_at, $customer_id, $performer_id, $amount, $description, $paid)) {
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
        PAID => $paid,
        AMOUNT => $amount
    ];
}

/**
 * Fetch price of specified task_id owned by customer_id
 *
 * @param $task_id
 * @param $customer_id
 * @return null|bool|int  null if the row with specified task_id/customer_id doesn't exists, price if there's such row and false if there was some errors
 */
function dal_task_fetch_unpaid_price($task_id, $customer_id)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "SELECT amount FROM db_task.task WHERE id=? AND customer_id=? AND NOT paid");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'ii', $task_id, $customer_id)) {
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
    if (!mysqli_stmt_bind_result($stmt, $amount)) {
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
    return $amount;
}

/**
 * Fetch the id of last task owned by customer_id
 *
 * @param $customer_id
 * @return null|bool|array null if there's no such row, assoc array containing last owned task if there's such row and false if there was some errors
 */
function dal_task_fetch_last($customer_id)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "SELECT id,amount,paid FROM db_task.task WHERE customer_id=? ORDER BY id DESC LIMIT 1");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'i', $customer_id)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_bind_result($stmt, $id, $amount, $paid)) {
        add_error($connection, $db_errors);
        return false;
    }
    mysqli_stmt_fetch($stmt);
    if (!mysqli_stmt_close($stmt)) {
        add_error($connection, $db_errors);
        return false;
    }
    return [
        ID => $id,
        AMOUNT => $amount,
        PAID => $paid
    ];
}

/**
 * Fetches tasks limited by limit for specified user_id and user_type with an ids less that specified id
 * with specified clause and apply callback function to each fetched entity
 *
 * @param $callback callable
 * @param $user_id integer
 * @param $paid string
 * @param $select_user_type string
 * @param $limit integer
 * @param $last_id integer
 * @return bool|null true if the fetch and callback were successful, false if there was some errors and null if there's no such row
 */
function dal_task_fetch_tasks_less_than_last_id_limit($callback, $user_id, $paid, $select_user_type, $limit = 100, $last_id = null)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $last_id_clause = $last_id === null ? '' : "AND id < $last_id";

    $query = "SELECT id, timestampdiff(SECOND, now(), created_at), customer_id, performer_id, amount, description, paid FROM db_task.task WHERE $paid AND not deleted AND $select_user_type <=> ? $last_id_clause ORDER BY id DESC LIMIT ?";
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
