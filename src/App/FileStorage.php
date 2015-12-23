<?php
namespace App;

class FileStorage
{
    private $storage;

    private $file;

    public function __construct($storage, $mode = 'w+')
    {
        $this->file = realpath($storage);

        $this->storage = fopen($storage, $mode);
    }

    public function __destruct()
    {
        fclose($this->storage);
    }

    public function read($position, $length)
    {
        if($position !== null) {

            $this->move($position);

        }

        return fread($this->storage, $length);
    }

    public function write($position, $text)
    {
        if($position !== null) {

            $this->move($position);

        }

        fwrite($this->storage, $text);

        return null;
    }

    public function move($position)
    {
        fseek($this->storage, $position);
    }

    public function handle()
    {
        return $this->storage;
    }

    public function file()
    {
        return $this->file;
    }

    public function ends()
    {
        return feof($this->storage);
    }

    public function readLine()
    {

    }
}