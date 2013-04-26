<?php
namespace M2E\Async;

class AbsoluteTimer extends Pollable
{
    private $time;
    private $callback;
    private $args;

    function __construct($time,$args,$callback)
    {
        if((float) $time != $time)
        {
            echo "Time must be an integer or a float\n";
            exit;
        }
        $this->time     = $time;
        $this->callback = $callback;
        $this->args     = $args;
    }

    function on_poll()
    {
        if(microtime(true) >= $this->time)
        {
            $func = $this->callback;
            $func($this, $this->args);
            $this->stop_polling(); 
        }
    }
}
