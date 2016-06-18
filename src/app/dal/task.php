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

/**
 * Set locked_balance status to task
 *
 * @param $task_id  integer
 * @return bool|null null if the row with specified task_id doesn't exists, true if update was successful and false if there was some errors
 */
function dal_task_update_set_balance_locked($task_id)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $mysqli_result = mysqli_query($connection, "UPDATE db_task.task SET balance_locked=TRUE WHERE id = $task_id");
    $success = false;
    if ($mysqli_result)
        $success = true;
    return $success;
}

/**
 * Set locked_balance status to task
 *
 * @param $task_id  integer
 * @return bool|null null if the row with specified task_id doesn't exists, true if update was successful and false if there was some errors
 */
function dal_task_update_set_paid($task_id)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $mysqli_result = mysqli_query($connection, "UPDATE db_task.task SET paid=TRUE WHERE id = $task_id");
    $success = false;
    if ($mysqli_result)
        $success = true;
    return $success;
}

/**
 * Set performer_id to task
 *
 * @param $task_id integer
 * @param $performer_id integer
 * @return bool|null null if the row with specified task_id doesn't exists, true if update was successful and false if there was some errors
 */
function dal_task_update_set_performer_id($task_id, $performer_id)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $mysqli_result = mysqli_query($connection, "UPDATE db_task.task SET performer_id=$performer_id WHERE id = $task_id AND (performer_id=$performer_id OR performer_id IS NULL)");
    $success = false;
    if ($mysqli_result)
        $success = true;
    return $success;
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
    $stmt = mysqli_prepare($connection, "UPDATE db_task.task SET deleted=TRUE WHERE id=? AND NOT balance_locked");
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
    $system_commission_percent = get_system_commission();

    if (!filter_var($system_commission_percent, FILTER_VALIDATE_INT) || $system_commission_percent < 0 || $system_commission_percent > 100) {
        die();
    }
    $amount_query = "(SELECT ($amount - $amount * ($system_commission_percent / 100)))";
    $commission_query = "(SELECT ($amount * ($system_commission_percent / 100)))";
    $stmt = mysqli_prepare($connection, "INSERT INTO db_task.task (customer_id, amount, commission, description) VALUES (?,$amount_query,$commission_query,?)");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'is', $customer_id, $description)) {
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
    $stmt = mysqli_prepare($connection, "SELECT id, timestampdiff(SECOND, now(), created_at), customer_id, performer_id, amount + commission, amount AS price, commission, description, balance_locked, paid FROM db_task.task WHERE id=?");
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
    if (!mysqli_stmt_bind_result($stmt, $id, $created_at, $customer_id, $performer_id, $amount, $price, $commission, $description, $balance_locked, $paid)) {
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
        BALANCE_LOCKED => $balance_locked,
        PAID => $paid,
        COMMISSION => $commission,
        PRICE => $price,
        AMOUNT => $amount
    ];
}

function dal_task_count_total_paid_commission()
{
    //TODO: cache balance
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $mysqli_result = mysqli_query(get_task_connection(), "SELECT COALESCE(sum(commission),0) AS balance FROM db_task.task WHERE paid=TRUE");
    if ($mysqli_result === false) {
        return false;
    }
    return mysqli_fetch_assoc($mysqli_result)['balance'];
}

/**
 * Fetch price of specified task_id owned by customer_id
 *
 * @param $task_id
 * @param $customer_id
 * @return null|bool|int  null if the row with specified task_id/customer_id doesn't exists, price if there's such row and false if there was some errors
 */
function dal_task_fetch_non_locked_price($task_id, $customer_id)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "SELECT amount+commission FROM db_task.task WHERE id=? AND customer_id=? AND NOT balance_locked");
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
    $mysqli_result = mysqli_query($connection, "SELECT id, amount + commission, paid, balance_locked FROM db_task.task WHERE customer_id=$customer_id ORDER BY ID DESC LIMIT 1");
    $result = mysqli_fetch_array($mysqli_result, MYSQLI_ASSOC);
    mysqli_free_result($mysqli_result);
    return $result;
}

/**
 * Fetches tasks limited by limit for specified user_id and user_type with an ids less that specified id
 * with specified clause and apply callback function to each fetched entity
 *
 * @param $callback callable
 * @param $user_id integer
 * @param $balance_locked string
 * @param $select_user_type string
 * @param $limit integer
 * @param $latest_task_id_query string
 * @param $last_id integer
 * @return bool|null true if the fetch and callback were successful, false if there was some errors and null if there's no such row
 */
function dal_task_fetch_tasks_complex_query_limit($callback, $user_id, $balance_locked, $select_user_type, $limit = 100, $latest_task_id_query, $last_id = null)
{
    $db_errors = initialize_db_errors();
    $connection = get_task_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $last_id_clause = $last_id === null ? '' : "AND id < $last_id";
    if (!$limit) {
        $limit = 100;
    }
    $query = "SELECT id, timestampdiff(SECOND, now(), created_at), amount as price, customer_id, performer_id, amount + commission as amount, description, balance_locked, paid FROM db_task.task WHERE $balance_locked $latest_task_id_query AND not deleted AND ($select_user_type <=> ?) $last_id_clause ORDER BY id DESC LIMIT ?";
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
    if (!mysqli_stmt_bind_result($stmt, $id, $created_at, $price, $customer_id, $performer_id, $amount, $description, $balance_locked, $paid)) {
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
            PRICE => $price,
            DESCRIPTION => $description,
            BALANCE_LOCKED => $balance_locked,
            PAID => $paid
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
