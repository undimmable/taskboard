<?php
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();
ignore_user_abort(true);
ini_set('max_execution_time', 0);
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/taskboard/src/app');
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/taskboard_config');
require_once 'config/constants.php';
require_once 'config/db_config.php';
require_once 'config/security_config.php';
require_once 'dal/event.php';
require_once 'events/event.php';
require_once 'dal/login.php';
require_once 'lib/helper.php';
require_once 'security/jwt.php';
require_once 'security/token_auth.php';
date_default_timezone_set('UTC');
$master = null;
$clients = [];
$forbidden_clients = [];
$clients_size = 0;
$critical_client_size = 500;

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

function send_event_to_client($client, $id, $str)
{
    $id = "id: $id\n";
    $event = "data: $str\n\n";
    log_debug("Sending event $id, $event to " . $client[USER_ID]);
    if (!is_resource($client['connection'])) {
        drop_client($client);
        return;
    }
    if (!(@socket_write($client['connection'], $id) && @socket_write($client['connection'], $event))) {
        log_info("Socket write has failed " . socket_strerror(socket_last_error()));
        drop_client($client);
    }
    if ($GLOBALS['debug_enabled'])
        log_debug("Event " . $event . " sent to client " . $client[USER_ID]);
}

function drop_client($client)
{
    global $clients_size, $clients;
    $clients_size--;
    if (is_resource($client['connection']))
        socket_close($client['connection']);
    if (($key = array_search($client, $clients)) !== false) {
        unset($clients[$key]);
    }
}

function remove_existing_client($user_agent, $client_ip, $target_id)
{
    global $clients;
    if (array_key_exists($target_id, $clients)) {
        foreach ($clients[$target_id] as $client) {
            if ($client['user_agent'] == $user_agent && $client['client_ip'] = $client_ip)
                drop_client($client);
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
            remove_existing_client($client['user_agent'], $client['client_ip'], $client['user_id']);
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

function parse_params($uri)
{
    $query = parse_url($uri, PHP_URL_QUERY);
    parse_str($query, $arr);
    return $arr;
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
    if ($method != "GET") {
        log_error("Doesn't seem like GET request, will drop.");
        return false;
    }
    $query_params = parse_params($uri);
    $headers = [];
    $auth_cookie = null;
    if (strpos($request, get_event_token_header()) < 0) {
        log_error("Request from unknown source, will drop");
        return null;
    }
    $event_csrf = null;
    $client_last_event_id = null;
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
            if ($key == 'Last-Event-ID') {
                $client_last_event_id = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            } else if ($key == 'X-Real-IP') {
                $client_ip = filter_var($value, FILTER_SANITIZE_STRING);
            } else if ($key == 'User-Agent') {
                $user_agent = filter_var($value, FILTER_SANITIZE_STRING);
            }
            $headers[$key] = $value;
        }
    }
    if (is_null($client_last_event_id)) {
        if (array_key_exists('lastEventId', $query_params)) {
            $client_last_event_id = filter_var($query_params['lastEventId'], FILTER_SANITIZE_NUMBER_INT);
        }
    }
    if (is_null($client_last_event_id)) {
        log_info("Client hasn't provided snapshot last event id, will drop");
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
    $user = parse_user_from_token(parse_token_from_string($auth_cookie));
    if (is_null($user) || !array_key_exists(ID, $user)) {
        log_info("Client provided malformed token, will drop");
        return null;
    }
    $user_id = $user[ID];
    $login = dal_login_fetch($user[ID], parse_ip($client_ip), parse_user_client($user_agent));
    if (!$login) {
        log_info("Client provided token for unauthenticated user $user_id, will drop");
        return null;
    }
    return [
        'user_id' => $user_id,
        'client_ip' => $client_ip,
        'user_agent' => $user_agent,
        'connection' => $connection,
        'user_email' => $user[EMAIL],
        'last_event_id' => $client_last_event_id
    ];
}

function fetch_events()
{
    foreach ($GLOBALS['clients'] as &$client_array) {
        foreach ($client_array as &$existing_client) {
            $ev = fetch_generic_event($existing_client[USER_ID], $existing_client['last_event_id']);
            if ($ev && count($ev) > 0 && array_key_exists('id', $ev) && $ev['id']) {
                if ($GLOBALS['debug_enabled']) {
                    log_debug("Fetched events" . $ev['ev_list']);
                }
                var_dump($GLOBALS['clients']);
                $existing_client['last_event_id'] = (int)$ev['id'];
                var_dump($GLOBALS['clients']);
                send_event_to_client($existing_client, $existing_client['last_event_id'], $ev['ev_list']);
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
