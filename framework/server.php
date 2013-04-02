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

                        $request_class = $this->config['request_class'];
                        echo "Serving: ".$req->path."\n";
                        $ereq = new $request_class($this, $req);

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
    }
}

class ERequest
{
    var
        $_req,
        $_sess,
        $_serv,
        $headers,
        $content_type = 'text/html';

    function __construct($serv, $req)
    { 
        $this->_req  = $req;
        $this->_serv = $serv;

        $sess_class = $serv->config['session_class'];
        $this->_sess = new $sess_class($this);
    } 

    function get_request(){ return $this->_req;  }
    function get_session(){ return $this->_sess; }
    function get_server() { return $this->_serv; }

    function add_header($v){ $this->headers[] = $v; }

    function reply_http($body,$code=200,$status="OK")
    {
        $this->add_header("Content-Type: ".$this->content_type);
        $this->get_server()->_conn->reply_http($this->get_request(), $body, $code, $status, $this->headers);
        $this->_sess->save();
    }

    function redirect($page, $code=307,$status="Temporary Redirect")
    {
        $this->add_header("Location: $page");
        $this->reply_http('',$code,$status);
    }
}

abstract class Session
{
    var 
        $cookies,
        $sess_id,
        $_req,
        $sess_data = array(),
        $has_changes = false,
        $sess_cookie_name = "SESSID";

    abstract protected function save();
    abstract protected function load();

    function __construct($req)
    {
        $this->_req = $req;
        if(isset($req->get_request()->headers->cookie))
        {
            foreach(explode("; ", $req->get_request()->headers->cookie) as $cookie)
            {
                list($key, $value) = explode("=", $cookie);
                $this->cookies[$key] = $value;
            }
        }
        if($this->cookies[$this->sess_cookie_name])
        {
            $this->sess_id = preg_replace("#/#", "", $this->cookies[$this->sess_cookie_name]);
        }
        else
        {
            $rand = preg_replace("#/#", "", base64_encode(openssl_random_pseudo_bytes(32)));
            $this->add_cookie($this->sess_cookie_name, $rand);
        }
        $this->load();
    }

    public function add_cookie($key, $value, $expire=null)
    {
        if($expire != null)
        {
            $this->_req->add_header("Set-Cookie: ".$key."=".$value."; expires=".$expire);
        }
        else
        {
            $this->_req->add_header("Set-Cookie: ".$key."=".$value);
        }
    }

    public function get($key)
    {
        return array_key_exists($key, $this->sess_data) ? $this->sess_data[$key] : null;
    }

    public function set($key, $val)
    {
        $this->has_changes = true;
        $this->sess_data[$key] = $val;
    }
}

class FilesystemSession extends Session
{
    private $file_path;

    public function save()
    {
        if($this->has_changes)
        {
            file_put_contents($this->file_path, json_encode($this->sess_data));
        }
    }

    public function load()
    {
        $this->file_path = getcwd()."/tmp/sess/".$this->cookies[$this->sess_cookie_name]; 
        if(file_exists($this->file_path))
        {
            $this->sess_data = (array)json_decode(file_get_contents($this->file_path));
        }
        else
        {
            $this->sess_data = array();
            $this->has_changes = 1;
        }    
    }
}
