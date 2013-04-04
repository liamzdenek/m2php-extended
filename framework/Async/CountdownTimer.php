<?php

class CountdownTimer extends AbsoluteTimer
{
    function __construct($rel_time, $args, $callback)
    {
        echo "currently ".microtime(true)." - firing in $rel_time at ".(microtime(true)+$rel_time)."\n";
        parent::__construct(microtime(true)+$rel_time, $args, $callback);
    }
}
