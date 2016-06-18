<?php
/**
 * Event functions
 *
 * PHP version 5
 *
 * @category  EventFunctions
 * @package   Events
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */
/**
 * require dal helper
 */
require_once "dal/event.php";
/**
 * @param $entity_id        integer Id of an entity
 * @param $json             string Event object
 * @param $type             string Event type
 */
function send_generic_event($entity_id, $json, $type = 'c')
{
    write_event($entity_id, $json, $type);
}

/**
 * Apply callback for fetched event
 *
 * @param $entity_id integer
 * @param $last_event_id
 * @return array|bool|null
 */
function fetch_generic_event($entity_id, $last_event_id)
{
    return fetch_events_after($entity_id, $last_event_id, 1000);
}
