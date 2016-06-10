<?php
/**
 * SSE functions draft
 *
 * PHP version 5
 *
 * @category  APIFunctions
 * @package   Api
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */
/**
 * Require application bootstrap
 */
require_once "../bootstrap.php";
$event = null;

/**
 * Get event from pool
 * 
 * @return int|null
 */
function get_event()
{
    global $event;
    $i = 0;
    while (is_null($event) && $i < 20) {
        $i++;
        sleep(1);
    }
    return is_null($event) ? 0 : $event;
}


header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
echo "data: " . get_event() . PHP_EOL;
echo PHP_EOL;
flush();