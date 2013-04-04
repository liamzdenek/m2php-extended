m2php-extended (working name)
=============================

* Mongrel2: <http://mongrel2.org>
* Mongrel2 Python Library: <http://sheddingbikes.com/posts/1279007133.html>
* Original m2php: <https://github.com/winks/m2php>

Requirements
------------

* ZeroMQ 2.0.7 or later: <http://www.zeromq.org/>
* PHP 5.3: <http://php.net>
* ZeroMQ PHP bindings: <http://www.zeromq.org/bindings:php>

Purpose
-------
This framework was designed to extend m2php with commonly needed functionality (such as route handling) in the simplest and fastest way possible. Ideally, this framework should be small enough that any developer can read through the entire codebase in under an hour.

Example
-------

See example/hello_world/hello.php for the latest version

```php
require "../framework/server.php";
$config = array
(
    'uuid' => '82209006-86FF-4982-B5EA-D1E29E55D481',
    'sub_addr' => 'tcp://127.0.0.1:9997',
    'pub_addr' => 'tcp://127.0.0.1:9996',
    
    'routes' => array
    (
        '#/(.*)#' => 'default',
    ),

    'handlers' => array
    (
        'default' => array('DefaultController', 'default_action'),
    ),
);

class DefaultController
{
    function default_action($conn, $req, $args)
    {
        $conn->reply_http($req, "Hello, World!"); 
    }
}

$server = new Server($config);
$server->run();
```

Basics
======

Routes
------
A route is a regular expression that points to a handler. When a regular expression matches the URI, the corresponding handler is called.

Handlers
--------
A handler points to a function in a class. When a handler is called, an instance of that class is created, and the corresponding function within it is called.

Session
-------
The session represents all of the data associated with the user. Abstract class "Session" handles all of the basic adding/removing data and cookie management. FilesystemSession extends Session and adds basic disk I/O. For an example of sessions, see examples/session_demo

Async
-----
An empowering, simple, and fast async handler is built in to this framework. This means that a single instance of the application can handle multiple requests from multiple users concurrently. This is particularly useful in long polling. See examples/async_hello_world. For a much more complex example, see examples/long_polling_chat

