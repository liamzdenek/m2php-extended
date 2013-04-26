<?php

require "../../framework/Server.php";
$config = array
(
    'uuid' => '82209006-86FF-4982-B5EA-D1E29E55D481',
    'sub_addr' => 'tcp://127.0.0.1:9997',
    'pub_addr' => 'tcp://127.0.0.1:9996',
    
    'routes' => array
    (
        '/login'  => 'login_action',
        '/logout' => 'logout_action',
        '/%s'     => 'login_page',
    ),

    'handlers' => array
    (
        'login_page'    => array('DefaultController', 'login_page'),
        'login_action'  => array('DefaultController', 'login_action'),
        'logout_action' => array('DefaultController', 'logout_action'),
    ),
);

class DefaultController
{
    public $login_html = null; 

    function __construct()
    {
        $this->login_html = 
            '<form method="post" action="/login">'.
                'Username: <input name="username"><br/>'.
                'Password: <input type="password" name="password"><br/>'.
                '<input type="submit">'.
            '</form>';
    }

    function login_page($req)
    {
        $username = $req->get_session()->get("username");
        if(empty($username))
        {
            $req->reply_http($this->login_html);
        }
        else
        {
            $req->reply_http
            (
                '<p>You\'re logged in under the username "'.$req->get_session()->get("username").'"</p>'.
                '<a href="/logout">Logout</a>'
            );
        }
    }

    function login_action($req)
    {
        parse_str($req->get_request()->body, $data);
        if($data['password'] != "asdf")
        {
            $req->reply_http("<p>Invalid username or password</p>".$this->login_html);
        }
        else
        {
            $req->get_session()->set("username", $data['username']);
            $req->redirect("/"); 
        }
    }

    function logout_action($req)
    {
        $username = $req->get_session()->get("username");
        if(!empty($username))
        {
            echo "Logging you out\n";
            $req->get_session()->remove("username");
        }
        $req->redirect("/");
    }
}
$server = new \M2E\Server($config);
$server->run();

