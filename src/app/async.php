<?php
function swrite($socket, $str)
{
    if (!socket_write($socket, $str)) {
        echo "Socket write has failed " . socket_strerror(socket_last_error());
    }
}

error_reporting(E_ALL);
set_time_limit(0);
date_default_timezone_set('UTC');
ob_implicit_flush();
$clients = array();
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($socket, '127.0.0.1', 3000);
socket_listen($socket);
socket_set_nonblock($socket);
$iter = 0;
while (true) {
    if (($connection = socket_accept($socket)) !== false) {
        $clients[] = $connection;
    }
    $iter++;
    if ($iter >= 10) {
        $iter = 0;
        $dt = date(DATE_RFC2822);
        echo "Write to socket " . $dt . "\n";
        $second = 0;
        foreach ($clients as $client) {
            swrite($client, $dt . "\n");
            if ($second == 1) {
                $second = 0;
                socket_close($client);
            }
            $second++;
        }
    }
    sleep(1);
}
?>
