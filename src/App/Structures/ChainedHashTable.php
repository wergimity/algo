<?php
namespace App\Structures;

use App\FileStorage;

class ChainedHashTable
{
    /**
     * @var FileStorage
     */
    private $storage;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $addressLength;

    /**
     * @var int
     */
    private $fullLength;

    /**
     * @var int
     */
    private $nextAddress;

    public function __construct(FileStorage $storage, $size, $length)
    {
        $this->storage = $storage;
        $this->size = $size;
        $this->length = $length;
        $this->addressLength = ceil(log10($size));
        $this->fullLength = $this->length + $this->addressLength * 2;
        $this->nextAddress = 0;
    }

    public function insert($file)
    {
        $file = $this->fix($file, $this->length);

        $node = $this->findNode($file, $index, $attach);

        if($node === null && $attach) {

            $this->attach($index, ++$this->nextAddress);

            $index = $this->size + $this->nextAddress;

            return $this->write($index, $file);

        }

        if($node !== null) {

            $value = $this->value($node);

            return $this->write($index, $file, $value + 1);

        }

        return $this->write($index, $file);
    }

    public function search($file)
    {
        step(); $file = $this->fix($file, $this->length);

        step(); return $this->findNode($file);
    }

    private function findNode($file, &$index = null, &$attach = false)
    {
        step(); $current = trim($file);

        step(); $index = $this->hash($file);

        step(); $node = $this->read($index);

        step(); $key = $this->key($node);

        step(); $next = $this->next($node);

        step(); if ($key == '') return null;

        step(); while($next !== null && $key != $current) {

        step(); $index = $this->size + $next;

        step(); $node = $this->read($index);

        step(); $key = $this->key($node);

        step(); $next = $this->next($node);
        }

        step(); if($key == $current) {

        step(); return $node;

        }

        step(); $attach = true;

        step(); return null;
    }

    private function attach($address, $next)
    {
        $position = $address * $this->fullLength + $this->length + $this->addressLength;

        $next = $this->fix($next, $this->addressLength);

        $this->storage->write($position, $next);
    }

    private function write($address, $key, $value = 1, $next = null)
    {
        $position = $address * $this->fullLength;

        $key = $this->fix($key, $this->length);

        $value = $this->fix($value, $this->addressLength);

        $next = $next === null ? '' : $this->fix($next, $this->addressLength);

        $this->storage->write($position, $key . $value . $next);
    }

    private function read($address)
    {
        step(); $position = $address * $this->fullLength;

        step(); return $this->storage->read($position, $this->fullLength);
    }

    public function key($node)
    {
        step(); return trim(substr($node, 0, $this->length));
    }

    public function value($node)
    {
        step(); return (int) substr($node, $this->length, $this->addressLength);
    }

    private function next($node)
    {
        step(); $result = substr($node, $this->length + $this->addressLength);

        step(); if(trim($result) == '') return null;

        step(); return (int) $result;
    }

    private function fix($value, $length)
    {
        step(); $result = substr($value, 0, $length);

        step(); return str_pad($result, $length, ' ');
    }

    private function hash($name)
    {
        step(); $result = 0;

        for (step(), $i = 0; step(), $i < 3; step(), $i++) {
            step(); $char = @ord($name[$i]);

            step(); $result += $char * pow(256, $i);
        }

        step(); return $result % $this->size;
    }
}