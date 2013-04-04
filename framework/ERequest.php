<?php

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
    } 

    function get_request(){ return $this->_req;  }
    function get_server() { return $this->_serv; }
    function get_async()  { return $this->_serv->_looper; }
    function get_session()
    {
        if(isset($this->_sess))
        {
            return $this->_sess;
        }
        else
        {
            $sess_class = $this->get_server()->config['session_class'];
            return $this->_sess = new $sess_class($this);
        }
    }

    function add_header($v){ $this->headers[] = $v; }

    function reply_http($body,$code=200,$status="OK")
    {
        $this->add_header("Content-Type: ".$this->content_type);
        $this->get_server()->_conn->reply_http($this->get_request(), $body, $code, $status, $this->headers);
        if(isset($this->_sess))
        {
            $this->_sess->save();
        }
    }

    function redirect($page, $code=307,$status="Temporary Redirect")
    {
        $this->add_header("Location: $page");
        $this->reply_http('',$code,$status);
    }

    function forward($handler)
    {
        if(!isset($this->get_server()->config['handlers'][$handler]))
        {
            throw new Exception("No such handler '$handler'");
        }
        $class_name = $this->get_server()->config['handlers'][$handler][0];
        $func_name  = $this->get_server()->config['handlers'][$handler][1];
        $class = new $class_name;
        $class->$func_name($this);
    }
}
