<?php

define("S_MYSQL", 0);

require "../../framework/Server.php";
$config = array
(
    'uuid' => '82209006-86FF-4982-B5EA-D1E29E55D481',
    'sub_addr' => 'tcp://127.0.0.1:9997',
    'pub_addr' => 'tcp://127.0.0.1:9996',
    
    'routes' => array
    (
        '/set' => 'set',
        '/' => 'default',
    ),

    'handlers' => array
    (
        'default' => array('DefaultController', 'default_action'),
        'set'     => array('DefaultController', 'set_action'),
    ),
);

class DefaultController
{
    function default_action($req)
    {
        $db = $req->get_singleton(S_MYSQL);
        $q = $db->query("SELECT * FROM test");
        $res = array();
        while($r = $q->fetch_assoc())
        {
            $res[$r['k']] = $r['v'];
        }
        $req->reply_http(json_encode($res,true)); 
    }

    function set_action($req)
    {
        $db = $req->get_singleton(S_MYSQL);

        $key = $req->get_uri_arg("k");
        $val = $req->get_uri_arg("v");

        if(!isset($key) || !isset($val))
        {
            $req->reply_http("[error:'syntax /set?k=[key]&v=[value]']");
            return;
        }

        $exists = $db->query("SELECT * FROM test WHERE k='".$db->escape_string($key)."';");

        if($exists === null)
        {
            $req->reply_http("[error:'internal error - ".$db->error."']");
        }
        else if($row = $exists->fetch_assoc())
        {
            if(strlen($val) == 0)
            {
               $db->query("DELETE FROM test WHERE k='".$db->escape_string($key)."'"); 
            }
            else
            {
                $db->query("UPDATE test SET v='".$db->escape_string($val)."' WHERE k='".$db->escape_string($key)."'");
            }
        }
        else
        {
            $db->query("INSERT INTO test VALUES ('".$db->escape_string($key)."','".$db->escape_string($val)."');");
        }

        if($db->error)
        {
            $req->reply_http("[error:".$db->error."]");
        }
        else
        {
            $req->reply_http("[error:0]");
        }
    }
}

$mysql = new \M2E\Singleton\MySQL("localhost", "root", "", "test");
$server = new \M2E\Server($config);

$server->set_singleton(S_MYSQL, $mysql);

$server->run();

