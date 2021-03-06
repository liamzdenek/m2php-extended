<?php
namespace M2E\Async;

abstract class Pollable
{
    public $_id;
    public $_looper;

    public function on_add(){}
    public function on_remove(){}

    abstract protected function on_poll();

    function stop_polling()
    {
        $this->_looper->remove($this);
    }
}
