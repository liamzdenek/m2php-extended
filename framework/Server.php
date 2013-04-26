<?php
namespace M2E;
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
        $class_cache = array(),
        $singletons = array();

    function get_singleton($key)
    {
        return $this->singletons[$key];
    }

    function set_singleton($key, $singleton)
    {
        $this->singletons[$key] = $singleton;
    }

    function __construct($config)
    {
        $this->config = $config;

        # verify that all of the handlers and methods exist
        foreach($this->config['handlers'] as $handler => $m)
        {
            $class_name = $m[0];
            $func_name  = $m[1];
            if(!class_exists($class_name))
            {
                throw new Exception("No such class '$class_name' (via handler '$handler')");
            }
            $this->class_cache[$class_name] = $class = new $class_name; 
            if(!method_exists($class,$func_name))
            {
                throw new Exception("No such function '$func_name' in '$class_name' (via handler '$handler')");
            }
        }

        foreach($this->config['routes'] as $regex=>$handler)
        {
            if(!isset($this->config['handlers'][$handler]))
            {
                throw new Exception("No such handler '$handler' as required by path '$regex'");
            }
        }

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
                    $args = sscanf($req->path, $regex);
                    
                    if(is_array($args))
                    {
                        $class_name = $serv->config['handlers'][$handler][0];
                        $func_name  = $serv->config['handlers'][$handler][1];
                        $class = $serv->class_cache[$class_name];
                        
                        $request_class = $serv->config['request_class'];
                        $ereq = new $request_class($serv, $req, $args);
                        $ereq->url_args = $args;

                        $class->$func_name($ereq);
                        break;
                    }
                }
            }
        );
        $this->_looper = new \M2E\Async\Looper;
        $this->_looper->add($this->_conn);

        if(!isset($this->config['session_class']))
        {
            $this->config['session_class'] = '\M2E\FilesystemSession';
        }
        if(!isset($this->config['request_class']))
        {
            $this->config['request_class'] = '\M2E\ERequest';
        }
    }

    function run()
    {
        $this->_looper->run();
        return;
    }
}
