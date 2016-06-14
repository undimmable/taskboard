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
require('bootstrap.php');
$master = null;
$clients = [];
$clients_size = 0;
$critical_client_size = 500;

function str_from_mem(&$value)
{
    $i = strpos($value, "\0");
    if ($i === false) {
        return $value;
    }
    $result = substr($value, 0, $i);
    return $result;
}

function send_event_to_client($client, $str)
{
    $event = 'data:' . $str . "\n\n";
    print("Sending event " . $event);
    if (!is_resource($client['connection'])) {
        drop_client($client);
        return;
    }
    if (!@socket_write($client['connection'], $event)) {
        echo "Socket write has failed " . socket_strerror(socket_last_error());
        drop_client($client);
    }
    print("Event sent " . $event);
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
    if (socket_last_error($master)) {
        socket_clear_error($master);
    } else {
        if ($clients_size >= $critical_client_size) {
            @socket_close($client['connection']);
        } else {
            $clients_size++;
            if (!array_key_exists($client[USER_ID], $clients)) {
                $clients[$client[USER_ID]] = [];
            }
            $clients[USER_ID][] = $client;
            @socket_write($client['connection'], "HTTP/1.1 200 OK\r\nContent-Type: text/event-stream\r\nCache-Control: no-cache\r\nConnection: keep-alive\r\nX-Frame-Options: SAMEORIGIN\r\nX-Xss-Protection:1; mode=block\r\nX-Content-Type-Options: nosniff\r\n\r\n");
        }
    }
}

function parse_client($connection)
{
    $read = socket_recv($connection, $request, 2048, MSG_DONTWAIT);
    if (!$read) {
        return false;
    }
    $lines = explode("\n", $request);
    list($method, $uri) = explode(' ', array_shift($lines));
    $headers = [];
    $auth_cookie = null;
    if (strpos($request, get_event_token_header()) < 0) {
        return false;
    }
    $event_csrf = null;
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
            }
            $headers[$key] = $value;
        }
    }
    if (is_null($event_csrf)) {
        return false;
    }
    $_SERVER['REMOTE_ADDR'] = array_key_exists('X-Real-IP', $headers) ? $headers['X-Real-IP'] : '';
    $_SERVER['HTTP_USER_AGENT'] = array_key_exists('User-Agent', $headers) ? $headers['User-Agent'] : '';
    if ($auth_cookie) {
        $user = parse_user_from_token(parse_token_from_string($auth_cookie));
        if (is_null($user) || !array_key_exists(ID, $user))
            return false;
        if ($event_csrf != get_event_csrf($user[ID], get_secrets_payload($user))) {
            return false;
        }
        $login = dal_login_fetch($user[ID], parse_ip(), parse_user_client());
        if (!$login)
            return false;
        return ['method' => strtoupper($method), 'uri' => $uri, 'headers' => $headers, 'user_id' => $user[ID], 'connection' => $connection, 'user_email' => $user[EMAIL], 'last_event_timestamp' => $headers['X-CURRENT-SNAPSHOT-TIMESTAMP']];
    } else {
        return false;
    }
}

//$context = stream_context_create(
//    [
//        'ssl' => [
//            'local_cert' => '/etc/ssl/taskboards.top.cert',
//            'allow_self_signed' => true,
//            'disable_compression' => true,
//            'verify_peer' => false,
//            'ciphers' => 'ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-SHA384:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA:ECDHE-RSA-AES128-SHA:DHE-RSA-AES256-SHA256:DHE-RSA-AES128-SHA256:DHE-RSA-AES256-SHA:DHE-RSA-AES128-SHA:ECDHE-RSA-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:AES256-GCM-SHA384:AES128-GCM-SHA256:AES256-SHA256:AES128-SHA256:AES256-SHA:AES128-SHA:DES-CBC3-SHA:HIGH:!aNULL:!eNULL:!EXPORT:!DES:!MD5:!PSK:!RC4',
//
//        ]
//    ]
//);
$socket_file = "/var/www/taskboards-events.sock";
$master = socket_create(AF_UNIX, SOCK_STREAM, 0);
if (!$master) {
    error_log("Couldn't create socket, " . socket_strerror(socket_last_error($master)));
    die(2);
}
@unlink($socket_file);
if (!@socket_bind($master, $socket_file)) {
    error_log("Couldn't bind socket, " . socket_strerror(socket_last_error($master)));
    die(2);
}
@socket_set_nonblock($master);
@socket_listen($master, $critical_client_size);
$iter = 0;
for (; ;) {
    $connection = socket_accept($master);
    $iter++;
    if ($connection) {
        $client = parse_client($connection);
        if ($client) {
            add_client($client);
        } else {
            @socket_close($connection);
        }
    }
    if ($iter == 10) {
        $iter = 0;
        foreach ($clients as $user_id => $client) {
            foreach ($client as $cl) {
                $id = $cl[USER_ID];
                $shm_id = shm_attach($id, 1024);
                if (($var = shm_get_var($shm_id, 0))) {
                    var_dump($var);
                    send_event_to_client($cl, $var);
                }
            }
        }
    }
    sleep(1);
}
@socket_close($master);
die(2);