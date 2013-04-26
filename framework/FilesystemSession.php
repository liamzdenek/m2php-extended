<?php
namespace M2E;

class FilesystemSession extends Session
{
    private $file_path;

    public function save()
    {
        if($this->has_changes)
        {
            file_put_contents($this->file_path, json_encode($this->sess_data));
        }
    }

    public function load()
    {
        $this->file_path = getcwd()."/tmp/sess/".$this->cookies[$this->sess_cookie_name]; 
        if(file_exists($this->file_path))
        {
            $this->sess_data = (array)json_decode(file_get_contents($this->file_path));
        }
        else
        {
            $this->sess_data = array();
            $this->has_changes = 1;
        }    
    }
}
