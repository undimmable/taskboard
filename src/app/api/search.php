<?php

require_once "../bootstrap.php";
require_once "dal/text_idx.php";
$routes = [
    'POST' => [
    ],
    'GET' => [
        ROOT => 'api_search'
    ],
    'PUT' => [],
    'DELETE' => []
];

function api_search()
{
    $text = $_GET['q'];
    $object = find_object($text);
    if (!$object) {
        render_not_found();
    } else {
        echo $object;
    }
}

route_request($routes);