<?php

class PeriodicTimer extends Pollable
{
    private $period;
    private $callback;
    private $args;
    private $last_poll = 0;

    function __construct($period,$args,$callback)
    {
        if((float) $period != $period)
        {
            echo "Period must be an integer or a float\n";
            exit;
        }
        $this->period    = $period;
        $this->callback  = $callback;
        $this->args      = $args;
        $this->last_poll = microtime();
    }

    function on_poll()
    {
        $mtime = microtime();
        if($this->last_poll+$this->period <= $mtime)
        {
            $this->last_poll = $mtime;
            $func = $this->callback;
            $func($args);
        }
    }
}
