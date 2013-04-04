<?php

require "../../framework/Server.php";
$config = array
(
    'uuid' => '82209006-86FF-4982-B5EA-D1E29E55D481',
    'sub_addr' => 'tcp://127.0.0.1:9997',
    'pub_addr' => 'tcp://127.0.0.1:9996',
    
    'routes' => array
    (
        '#/chat_submit#' => 'say',
        '#/chat_poll#'   => 'poll',
        "#/favicon.ico#" => 'favicon',
        '#/(.*)#'        => 'default',
    ),

    'handlers' => array
    (
        'default' => array('DefaultController', 'default_action'),
        'poll'    => array('DefaultController', 'poll'),
        'say'     => array('DefaultController', 'say'),
        'favicon' => array('DefaultController', 'favicon'),
    ),
);

global $users;
$users = array();

class DefaultController
{
    function default_action($req)
    {
        $str = <<<__EOS__
<head>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script>
        function send_chat(str)
        {
            $.ajax("/chat_submit?q="+encodeURIComponent(str));
            return false;
        }
        function do_poll()
        {
            $.ajax("/chat_poll",{
                success: function(d){
                    d = JSON.parse(d);
                    for(i in d)
                    {
                        document.getElementById("chatlog").innerHTML += d[i]+"<br/>"; 
                    }
                },
                complete: function(d){
                    do_poll();
                }
            });
        }
    </script>
</head>
<body onload="do_poll()">
    <div id="chatlog"></div>
    <form onsubmit="return send_chat(this.string.value);">
        <input id="string">
        <input type="submit">
    </form>
</body>
__EOS__;
        if("" == $req->get_session()->get("uuid"))
        {
            $req->get_session()->set("uuid", uniqid());
        }
        global $users;

        $users[$req->get_session()->get("uuid")] = array('last_poll'=>microtime(true),'messages'=>array());
        $req->reply_http($str);
    }
    function poll($req)
    {
        $users[$req->get_session()->get("uuid")]['last_poll'] = microtime(true);
        $req->get_async()->add
        (
            new PeriodicTimer
            (
                0.5, // seconds
                $req,
                function($timer, $req)
                {
                    global $users;
                    $messages = $users[$req->get_session()->get("uuid")]['messages'];
                    if(0 < count($messages))
                    {
                        $users[$req->get_session()->get("uuid")]['messages'] = array();
                        $req->reply_http(json_encode($messages));
                        $timer->stop_polling();
                    }    
                    if($timer->count >= 20)
                    {
                        $req->reply_http('[]');
                        $timer->stop_polling();
                    }
                }
            )
        );
    }
    function say($req)
    {
        global $users;
        $parts = array();
        parse_str($req->get_request()->headers->QUERY, $parts);
        foreach($users as $uuid=>&$user)
        {
            if($user['last_poll']+300 < microtime(true))
            {
                unset($users[$uuid]);
            }
            else
            {
                $user['messages'][] = $parts['q'];
            }
        }
        echo "> ".$parts['q']."\n";
        $req->reply_http("OK");
    }
    function favicon($req){$req->reply_http("", 404, "Not Found");}
}

$server = new Server($config);
$server->run();

