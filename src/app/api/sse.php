<?php
require_once "../bootstrap.php";
$event = null;

function getEvent()
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
echo "data: " . getEvent() . PHP_EOL;
echo PHP_EOL;
flush();