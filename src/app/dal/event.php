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

function dal_fetch_events_after($target_id, $target_role, $last_event_id, $limit)
{
    $connection = get_event_connection();
    if (!$connection) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    $target_role = filter_var($target_role, FILTER_SANITIZE_NUMBER_INT);
    if (!$target_role) {
        $target_role = get_role_key(PERFORMER);
    }
    $mysqli_result = @mysqli_query($connection, "SELECT max(ev.id) AS id, concat('[', group_concat(ev.event_list), ']') as ev_list FROM (SELECT max(id) as id, concat('{\"', type, '\":[', group_concat(DISTINCT concat('\"', message, '\"')), ']}') AS event_list FROM (SELECT type, message, id FROM db_event.event WHERE id > $last_event_id AND (target_id = $target_id OR target_id is null) AND target_role=$target_role LIMIT $limit) AS t_event GROUP BY t_event.type) AS ev");
    $result = mysqli_fetch_array($mysqli_result, MYSQLI_ASSOC);
    @mysqli_free_result($mysqli_result);
    return $result;
}

function dal_last_event_id()
{
    $db_errors = initialize_dal_errors();
    $connection = get_event_connection();
    if (!$connection) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    $mysqli_result = mysqli_query($connection, "SELECT max(id) AS id FROM db_event.event;");
    $result = mysqli_fetch_array($mysqli_result, MYSQLI_ASSOC);
    mysqli_free_result($mysqli_result);
    return $result;
}

function dal_write_event($entity_id = "NULL", $target_role, $message, $type)
{
    if (is_null($entity_id))
        $entity_id = "NULL";
    if (is_null($target_role))
        $target_role = get_role_key(PERFORMER);
    $db_errors = initialize_dal_errors();
    $connection = get_event_connection();
    if (!$connection) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    $stmt = mysqli_prepare($connection, "INSERT INTO db_event.event (target_id, target_role, message, type) VALUES ($entity_id,?,?,?)");
    if (!$stmt) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($stmt, 'iss', $target_role, $message, $type)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_execute($stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (!mysqli_stmt_close($stmt)) {
        add_dal_error($connection, $db_errors);
        return false;
    }
    if (mysqli_errno($connection) !== 0) {
        return false;
    }
    $id = mysqli_insert_id($connection);
    return $id;
}
