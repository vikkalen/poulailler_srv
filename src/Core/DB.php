<?php
namespace App\Core;

use \Exception;
use \SQLite3;

class DB
{
    protected $filename;
    protected $driver;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function open()
    {
        $init = !file_exists($this->filename);

        if(!$this->driver)
        {
            $this->driver = new SQLite3($this->filename);
        }

        if($init)
        {
            $this->exec('CREATE TABLE users (id INTEGER PRIMARY KEY ASC, name TEXT, password TEXT)');
            $this->exec('CREATE TABLE commands (id INTEGER PRIMARY KEY ASC, name STRING, params BLOB)');
        }

        return $this;
    }

    protected function exec($query)
    {
        if(!$this->driver->exec($query))
        {
            throw new Exception($this->driver->lastErrorMsg());
        };
    }

    protected function querySingle($query, $entireRow = false)
    {
        $result = $this->driver->querySingle($query, $entireRow);
        if($result === false)
        {
            throw new Exception($this->driver->lastErrorMsg());
        };

        return $result;
    }

    public function checkUser($name, $password)
    {
        $md5 = md5($password);
        $userId = $this->open()->querySingle('SELECT id FROM users WHERE name = "'
            . $this->driver->escapeString($name) . '" AND password = "'
            . $this->driver->escapeString($md5) . '"');
        
        return (bool)$userId;
    }

    public function pushCommand($command, $params = [])
    {
        $this->open()->exec('INSERT INTO commands (name, params) VALUES ("'
            . $this->driver->escapeString($command) . '",\''
            . $this->driver->escapeString(json_encode($params)) . '\')');

        return $this;
    }

    public function getCommand()
    {
        $row = $this->open()->querySingle('SELECT * from commands', true);

        if($row)
        {
            return [$row['name'], json_decode($row['params'], true)];
        }
        else
        {
            return [null, null];
        }
    }

    public function deleteCommand($command)
    {
        $this->open()->exec('DELETE FROM commands WHERE id = (SELECT id FROM commands WHERE name = "'
            . $this->driver->escapeString($command) . '" LIMIT 1)');

        return $this;
    }

}
