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
 * Require dal helper
 */

require_once 'dal/dal_helper.php';

function fetch_events_after($entity_id, $timestamp_ms, $limit = 10)
{
    $db_errors = initialize_db_errors();
    $connection = get_event_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $mysqli_result = mysqli_query($connection, "SELECT max(ev.ts) AS ts, concat('[', group_concat(ev.event_list), ']') as ev_list FROM (SELECT round(UNIX_TIMESTAMP(max(created_at)) * 1000) AS ts, concat('{\"', type, '\":[', group_concat(DISTINCT concat('\"', message, '\"')), ']}') AS event_list FROM (SELECT type, message, created_at FROM db_event.event WHERE target_id = $entity_id AND created_at > FROM_UNIXTIME($timestamp_ms/1000) LIMIT $limit) AS t_event GROUP BY t_event.type) AS ev");
    $result = mysqli_fetch_array($mysqli_result, MYSQLI_ASSOC);
    mysqli_free_result($mysqli_result);
    return $result;
}

function dal_now()
{
    $db_errors = initialize_db_errors();
    $connection = get_event_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $mysqli_result = mysqli_query($connection, "SELECT round(UNIX_TIMESTAMP(CURRENT_TIMESTAMP(3)) * 1000) as ts");
    $result = mysqli_fetch_array($mysqli_result, MYSQLI_ASSOC);
    mysqli_free_result($mysqli_result);
    return $result;
}

function write_event($entity_id, $message, $type)
{
    $db_errors = initialize_db_errors();
    $connection = get_event_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "INSERT INTO db_event.event (target_id, message, type) VALUES (?,?,?)");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'is', $entity_id, $message, $type)) {
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