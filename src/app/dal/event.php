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

function fetch_events_after($target_id, $last_event_id, $limit)
{
    $connection = get_event_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $mysqli_result = @mysqli_query($connection, "SELECT max(ev.id) AS id, concat('[', group_concat(ev.event_list), ']') as ev_list FROM (SELECT max(id) as id, concat('{\"', type, '\":[', group_concat(DISTINCT concat('\"', message, '\"')), ']}') AS event_list FROM (SELECT type, message, id FROM db_event.event WHERE id > $last_event_id AND (target_id = $target_id OR target_id is null) LIMIT $limit) AS t_event GROUP BY t_event.type) AS ev");
    $result = mysqli_fetch_array($mysqli_result, MYSQLI_ASSOC);
    @mysqli_free_result($mysqli_result);
    return $result;
}

function dal_last_event_id()
{
    $db_errors = initialize_db_errors();
    $connection = get_event_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $mysqli_result = mysqli_query($connection, "SELECT max(id) AS id FROM db_event.event;");
    $result = mysqli_fetch_array($mysqli_result, MYSQLI_ASSOC);
    mysqli_free_result($mysqli_result);
    return $result;
}

function write_event($entity_id = "NULL", $message, $type)
{
    if (is_null($entity_id))
        $entity_id = "NULL";
    $db_errors = initialize_db_errors();
    $connection = get_event_connection();
    if (!$connection) {
        add_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "INSERT INTO db_event.event (target_id, message, type) VALUES ($entity_id,?,?)");
    if (!$stmt) {
        add_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'ss', $message, $type)) {
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
