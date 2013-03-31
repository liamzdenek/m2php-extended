<?php
## potential names
## paradigm-php

## include the Mongrel2 framework
require __DIR__.'/Mongrel2/Connection.php';
require __DIR__.'/Mongrel2/Request.php';
require __DIR__.'/Mongrel2/Tool.php';

use Mongrel2\Connection;

class Server
{
    var
        $config = null,
        $_conn  = null;

    function __construct($config)
    {
        $this->config = $config;
        $this->_conn = new Connection($config['uuid'], $config['sub_addr'], $config['pub_addr']);
    }

    function run()
    {
        $conn = $this->_conn;
        while(true)
        {
            $req = $conn->recv();
            if($req->is_disconnect())
            {
                return;
            }

            $args = array();
            foreach($this->config['routes'] as $regex=>$handler)
            {
                if(preg_match($regex, $req->path, $args))
                {
                    try
                    {
                        if(!isset($this->config['handlers'][$handler]))
                        {
                            throw new Exception("No such handler '$handler'");
                        }
                        $class_name = $this->config['handlers'][$handler][0];
                        $func_name  = $this->config['handlers'][$handler][1];
                        $class = new $class_name;
                        if(!class_exists($class_name))
                        {
                            throw new Exception("No such class '$class_name' (via handler '$handler')");
                        }
                        if(!method_exists($class,$func_name))
                        {
                            throw new Exception("No such function '$func_name' in '$class_name' (via handler '$handler')");
                        }
                        array_shift($args);
                        $class->$func_name($conn, $req, $args);
                        break;
                    }
                    catch(Exception $e) 
                    {
                        echo 'Caught Exception: '.$e->getMessage()."\n";
                    }
                }
            }
        }
    }
}
