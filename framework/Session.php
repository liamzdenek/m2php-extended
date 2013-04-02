<?php

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

    // this is == unset... because fucking php won't let me name a function "unset"
    public function remove($key)
    {
        $this->has_changes = true;
        unset($this->sess_data[$key]);
    }
}
