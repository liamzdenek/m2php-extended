<?php

abstract class Pollable
{
    public $_id;
    public $_looper;

    public function on_add(){}
    public function on_remove(){}

    abstract protected function on_poll();
}
