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
