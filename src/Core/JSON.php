<?php
namespace App\Core;

class JSON
{
    protected $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function read()
    {
        if(!file_exists($this->filename)) return null;

        return json_decode(file_get_contents($this->filename), true);
    }

    public function write($data)
    {
        file_put_contents($this->filename, json_encode($data));
    }
}
