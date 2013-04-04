<?php

class Looper
{
    private $pollable = array();

    function run()
    {
        while(true)
        {
            foreach($this->pollable as $po)
            {
                if($po != null)
                {
                    $po->on_poll();
                }
            }
        }
    }

    function add($po)
    {
        if(!is_subclass_of($po, "Pollable"))
        {
            echo get_class($po)." is not a subclass of Pollable\n";
            exit(1);
        }

        $id     = 0;
        $got_id = false;
        for($id = 0;$id < PHP_INT_MAX;$id++)
        {
            if(!isset($this->pollable[$id]))
            {
                $got_id = true;
                break;
            }
        }
        if(!$got_id)
        {
            echo "Unable to get an ID. Did you happen to add more than ".PHP_INT_MAX." Pollable object to one Looper?";
            exit(1);
        }

        $po->_id = $id;
        $po->_looper = $this;
        $this->pollable[$id] = $po;

        $po->on_add();
    }

    function remove($po)
    {
        $id = $po->_id;
        $po->on_remove();
        $this->pollable[$id] = null;
    }
}
