<?php
namespace M2E\Singleton;

class MySQL extends \M2E\Singleton
{
    private $link;

    function __construct($host, $user, $pass, $db)
    {
        $this->link = new \mysqli($host, $user, $pass, $db);

        if(mysqli_connect_errno($this->link))
        {
            return mysqli_connect_error($this->link);
        }
        return true;
    }

    public function get()
    {
        return $this->link;
    }
};
