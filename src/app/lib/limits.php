<?php
/**
 * Lib functions
 *
 * PHP version 5
 *
 * @category  LibFunctions
 * @package   Lib
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */
$_max_connections_per_ip = 10;
$_max_requests = 10;
$_request_limit_timeout_sec = 60;
$_ip_storage = [];
$_requests_storage = [];

/**
 * Check if ip has reached the maximum connections limit
 * @param $ip
 * @return bool
 */
function check_ip_limits($ip)
{
    if (empty($ip)) {
        return false;
    }
    if (!isset ($_ipStorage[$ip])) {
        return true;
    }
    return ($_ipStorage[$ip] > $GLOBALS['_max_connections_per_ip']) ? false : true;
}

function check_request_limits($client_id)
{
    if (array_key_exists($client_id, $GLOBALS['_requests_storage'])) {
        $_requests_storage[$client_id] = ['last' => time(), 'total' => 1];
        return true;
    }

    if (time() - $GLOBALS['_requests_storage'][$client_id]['last'] > $GLOBALS['_request_limit_timeout_sec']) {
        $_request_storage[$client_id] = ['last' => time(), 'total' => 1];
        return true;
    }
    if ($GLOBALS['_requests_storage'][$client_id]['total'] > $GLOBALS['_max_requests']) {
        return false;
    }
    $GLOBALS['_requests_storage'][$client_id]['total']++;
    return true;

}
