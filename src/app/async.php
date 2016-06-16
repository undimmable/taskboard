<?php
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();
ignore_user_abort(true);
ini_set('max_execution_time', 0);
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/taskboard/src/app');
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/taskboard_config');
set_include_path(get_include_path() . PATH_SEPARATOR . '/Users/dimyriy/Development/projects/vk/taskboard/src/app');
set_include_path(get_include_path() . PATH_SEPARATOR . '/Users/dimyriy/Development/projects/vk/taskboard/config/php');
require_once 'config/constants.php';
require_once 'config/db_config.php';
require_once 'config/security_config.php';
require_once 'dal/event.php';
require_once 'events/event.php';
require_once 'dal/login.php';
require_once 'lib/helper.php';
require_once 'security/JWT.php';
require_once 'security/token_auth.php';
date_default_timezone_set('UTC');
$master = null;
$clients = [];
$forbidden_clients = [];
$clients_size = 0;
$critical_client_size = 500;
$debug_enabled = true;

function log_msg($msg, $file)
{
    error_log("[" . date('Y-m-d H:m:s:u') . "]" . $msg . "\n", 3, $file);
}

function log_info($msg)
{
    log_msg($msg, "/var/log/async_php_access.log");
}

function log_error($msg)
{
    log_msg($msg, "/var/log/async_php_error.log");
}

function log_debug($msg)
{
    global $debug_enabled;
    if ($debug_enabled)
        log_msg($msg, "/var/log/async_php_debug.log");
}

function send_event_to_client($client, $str)
{
    $event = 'data:' . $str . "\n\n";
    log_debug("Sending event " . $event . " to " . $client[USER_ID]);
    if (!is_resource($client['connection'])) {
        drop_client($client);
        return;
    }
    if (!@socket_write($client['connection'], $event)) {
        echo "Socket write has failed " . socket_strerror(socket_last_error());
        drop_client($client);
    }
    log_debug("Event " . $event . " sent to client " . $client[USER_ID]);
}

function drop_client($client)
{
    global $clients_size, $clients;
    $clients_size--;
    if (is_resource($client['connection']))
        socket_close($client['connection']);
    if (($key = array_search($client['connection'], $clients)) !== false) {
        $id = $client[USER_ID];
        unset($clients[$key]);
        if (!array_key_exists($id, $clients) || count($clients[$id]) === 0) {
            $shm_id = shm_attach($id);
            shm_detach($shm_id);
        }
    }
}

function add_client($client)
{
    global $clients_size, $master, $clients, $critical_client_size;
    $user_id = $client[USER_ID];
    log_info("Connected client $user_id");
    if (socket_last_error($master)) {
        socket_clear_error($master);
    } else {
        if ($clients_size >= $critical_client_size) {
            @socket_close($client['connection']);
        } else {
            $clients_size++;
            if (!array_key_exists($user_id, $clients)) {
                $clients[$user_id] = [];
            }
            $clients[$user_id][] = $client;
            socket_set_option($client['connection'], SOL_SOCKET, SO_KEEPALIVE, 1);
            @socket_write($client['connection'], "HTTP/1.1 200 OK\r\nContent-Type: text/event-stream\r\nCache-Control: no-cache\r\nConnection: keep-alive\r\nX-Frame-Options: SAMEORIGIN\r\nX-Xss-Protection:1; mode=block\r\nX-Content-Type-Options: nosniff\r\n\r\n");
        }
    }
}

function parse_client($connection)
{
    $read = socket_recv($connection, $request, 2048, MSG_DONTWAIT);
    log_debug($read);
    log_debug($request);
    if (!$read) {
        log_error("Couldn't parse client headers, will drop");
        return false;
    }
    $lines = explode("\n", $request);
    list($method, $uri) = explode(' ', array_shift($lines));
    $headers = [];
    $auth_cookie = null;
    if (strpos($request, get_event_token_header()) < 0) {
        log_error("Request from unknown source, will drop");
        return null;
    }
    $event_csrf = null;
    $client_snapshot_timestamp = null;
    $user_agent = null;
    $client_ip = null;
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, ': ') !== false) {
            list($key, $value) = explode(': ', $line);
            if ($key == 'Cookie') {
                if (strpos($value, PRIVATE_TOKEN) > -1) {
                    $auth_cookie = substr($value, strpos($value, PRIVATE_TOKEN) + 14);
                    if (($separator = strpos($auth_cookie, ';')) > -1)
                        $auth_cookie = substr($auth_cookie, 0, $separator);
                }
            }
            if ($key == 'X-CSRF-TOKEN') {
                $event_csrf = $value;
            } else if ($key == 'X-CURRENT-SNAPSHOT-TIMESTAMP') {
                $client_snapshot_timestamp = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            } else if ($key == 'X-Real-IP') {
                $client_ip = filter_var($value, FILTER_SANITIZE_STRING);
            } else if ($key == 'User-Agent') {
                $user_agent = filter_var($value, FILTER_SANITIZE_STRING);
            }
            $headers[$key] = $value;
        }
    }
    if (is_null($event_csrf)) {
        log_info("Couldn't parse event token, will drop");
        return null;
    }
    if (is_null($client_snapshot_timestamp)) {
        log_info("Client hasn't provided snapshot timestamp, will drop");
        return null;
    }
    if (is_null($client_ip)) {
        log_info("Client hasn't provided ip, will drop");
        return null;
    }
    if (is_null($user_agent)) {
        log_info("Client hasn't provided user-agent, will drop");
        return null;
    }
    if (is_null($auth_cookie)) {
        log_info("Client hasn't provided authentication cookie, will drop");
        return null;
    }
    $_SERVER['REMOTE_ADDR'] = $client_ip;
    $_SERVER['HTTP_USER_AGENT'] = $user_agent;
    $user = parse_user_from_token(parse_token_from_string($auth_cookie));
    if (is_null($user) || !array_key_exists(ID, $user)) {
        log_info("Client provided malformed token, will drop");
        return null;
    }
    $user_id = $user[ID];
    if ($event_csrf != get_event_csrf($user_id, get_secrets_payload($user_id))) {
        log_info("Client provided wrong token $event_csrf instead of get_secrets_payload($user_id), will drop");
        return null;
    }
    $login = dal_login_fetch($user[ID], parse_ip(), parse_user_client());
    if (!$login) {
        log_info("Client provided token for unauthenticated user $user_id, will drop");
//        return null;
    }
    return [
        'user_id' => $user_id,
        'connection' => $connection,
        'user_email' => $user[EMAIL],
        'last_event_timestamp' => $client_snapshot_timestamp
    ];
}

function fetch_events()
{
    global $clients;
    foreach ($clients as &$client_array) {
        foreach ($client_array as &$existing_client) {
            $ev = fetch_generic_event($existing_client[USER_ID], $existing_client['last_event_timestamp']);
            if ($ev && count($ev) > 0 && array_key_exists('ts', $ev) && $ev['ts']) {
                if ($GLOBALS['debug_enabled']) {
                    log_debug("Fetched events" . $ev['ev_list']);
                }
                $existing_client['last_event_timestamp'] = (int)$ev['ts'];
                send_event_to_client($existing_client, $ev['ev_list']);
            }
        }
    }
}

$socket_file = "/var/www/taskboards-events.sock";
$master = socket_create(AF_UNIX, SOCK_STREAM, 0);
if (!$master) {
    log_error("Couldn't create socket, dying. " . socket_strerror(socket_last_error($master)));
    die(2);
}
@unlink($socket_file);
socket_set_option($master, SOL_SOCKET, SO_KEEPALIVE, 1);
if (!@socket_bind($master, $socket_file)) {
    log_error("Couldn't bind socket, dying. " . socket_strerror(socket_last_error($master)));
    die(2);
}
if (!socket_set_nonblock($master)) {
    log_error("Couldn't set socket non blocking, switching to blocking io. " . socket_strerror(socket_last_error($master)));
}
if (!socket_listen($master, $critical_client_size)) {
    log_error("Couldn't listen on socket, dying. " . socket_strerror(socket_last_error($master)));
    die(2);
}
for (; ;) {
    $connection = socket_accept($master);
    if ($connection) {
        log_info("Socket accepted incoming connection " . $connection);
        $incoming_client = parse_client($connection);
        $client_str = print_r($incoming_client, true);
        log_info("Parsed client $client_str");
        if ($incoming_client) {
            add_client($incoming_client);
        } else {
            log_info("Dropping client $client_str due to previous errors");
            @socket_close($connection);
        }
    }
    fetch_events();
    sleep(1);
}
@socket_close($master);
die(2);