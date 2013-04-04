<?php
## potential names
## paradigm-php
## neutrino-php

## include this framework
require __DIR__.'/ERequest.php';
require __DIR__.'/Session.php';
require __DIR__.'/FilesystemSession.php';

## include this framework's async functionality
require __DIR__.'/Async/Looper.php';
require __DIR__.'/Async/Pollable.php';
require __DIR__.'/Async/AbsoluteTimer.php';
require __DIR__.'/Async/CountdownTimer.php';
require __DIR__.'/Async/PeriodicTimer.php';

## include the m2sh framework
require __DIR__.'/Mongrel2/Connection.php';
require __DIR__.'/Mongrel2/Request.php';
require __DIR__.'/Mongrel2/Tool.php';

use Mongrel2\Connection;

class Server
{
    var
        $config  = null,
        $_conn   = null,
        $_looper = null,
        $handler_cache = array();

    function __construct($config)
    {
        $this->config = $config;
        $this->_conn = new Connection
        (
            $config['uuid'], 
            $config['sub_addr'], 
            $config['pub_addr'],
            $this,
            function($serv, $req)
            {
                $args = array();
                foreach($serv->config['routes'] as $regex=>$handler)
                {
                    if(preg_match($regex, $req->path, $args))
                    {
                        try
                        {
                            if(!isset($serv->config['handlers'][$handler]))
                            {
                                throw new Exception("No such handler '$handler'");
                            }
                            
                            $class_name = $serv->config['handlers'][$handler][0];
                            $func_name  = $serv->config['handlers'][$handler][1];
                            $class      = null;

                            if(isset($serv->handler_cache[$class_name]))
                            {
                                $class = $serv->handler_cache[$class_name];
                            }
                            else
                            {
                                if(!class_exists($class_name))
                                {
                                    throw new Exception("No such class '$class_name' (via handler '$handler')");
                                }
                                $serv->handler_cache[$class_name] = $class = new $class_name; 
                            }
                            if(!method_exists($class,$func_name))
                            {
                                throw new Exception("No such function '$func_name' in '$class_name' (via handler '$handler')");
                            }                            
                            
                            array_shift($args);

                            $request_class = $serv->config['request_class'];
                            //echo "Serving: ".$req->path."\n";
                            $ereq = new $request_class($serv, $req);

                            $class->$func_name($ereq);
                            break;
                        }
                        catch(Exception $e) 
                        {
                            echo 'Caught Exception: '.$e->getMessage()."\n";
                        }
                    }
                }
            }
        );
        $this->_looper = new Looper;
        $this->_looper->add($this->_conn);

        if(!isset($this->config['session_class']))
        {
            $this->config['session_class'] = 'FilesystemSession';
        }
        if(!isset($this->config['request_class']))
        {
            $this->config['request_class'] = 'ERequest';
        }
    }

    function run()
    {
        $this->_looper->run();
        return;
    }
}
