<?php

require "../../framework/Server.php";
$config = array
(
    'uuid' => '82209006-86FF-4982-B5EA-D1E29E55D481',
    'sub_addr' => 'tcp://127.0.0.1:9997',
    'pub_addr' => 'tcp://127.0.0.1:9996',
    
    'routes' => array
    (
        '/%s' => 'default2',
        '/' => 'default',
    ),

    'handlers' => array
    (
        'default'  => array('DefaultController', 'default_action'),
        'default2' => array('DefaultController', 'default_2'),
    ),
);

class DefaultController
{
    function default_action($req)
    {
        $req->reply_http(json_encode(array("message"=>"Hello, World!"))); 
    }

    function default_2($req)
    {
        $req->reply_http("Hello, ".$req->url_args[0]."!");
    }
}

$server = new \M2E\Server($config);
$server->run();

