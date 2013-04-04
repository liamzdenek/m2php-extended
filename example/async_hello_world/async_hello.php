<?php

require "../../framework/Server.php";
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
    function default_action($req)
    {
        $req->get_async()->add
        (
            new CountdownTimer
            (
                1, // seconds 
                $req,
                function($req)
                {
                    $req->reply_http("Async args: ".print_r($req, true));
                }
            )
        );
    }
}

$server = new Server($config);
$server->run();
