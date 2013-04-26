<?php
namespace M2E\Async;

class CountdownTimer extends AbsoluteTimer
{
    function __construct($rel_time, $args, $callback)
    {
        parent::__construct(microtime(true)+$rel_time, $args, $callback);
    }
}
