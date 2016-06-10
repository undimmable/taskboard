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
 * @param $entity_id        integer Id of an entity
 * @param $json             string Event object
 * @param $type             string Event type
 */
function send_generic_event($entity_id, $json, $type)
{
    //do nothing
}

/**
 * @param $entity_id        integer Id of an entity
 * @param $entity_type      string Type of an entity
 * @param $text             string Text to index
 */
function send_index_event($entity_id, $entity_type, $text)
{
    $json = json_encode([
        'entity_id' => $entity_id,
        'entity_type' => $entity_type,
        'text' => $text
    ]);
    send_generic_event($entity_id, $json, 'text_idx');
}