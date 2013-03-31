<?php

require "../framework/server.php";
$config = array
(
    'uuid' => '82209006-86FF-4982-B5EA-D1E29E55D481',
    'sub_addr' => 'tcp://127.0.0.1:9997',
    'pub_addr' => 'tcp://127.0.0.1:9996',
    
    'routes' => array
    (
        '#/(.*)#' => 'default',
    ),

    'handlers' => array
    (
        'default' => array('DefaultController', 'default_action'),
    ),
);

class DefaultController
{
    function default_action($conn, $req, $args)
    {
        $conn->reply_http($req, "args: ".print_r($args, true)); 
    }
}

$server = new Server($config);
$server->run();

