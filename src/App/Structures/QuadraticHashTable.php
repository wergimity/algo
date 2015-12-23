<?php
namespace App\Structures;

use App\FileStorage;
use Exception;

class QuadraticHashTable
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

    private $c1 = 0;

    private $c2 = 1;

    public function __construct(FileStorage $storage, $size, $length)
    {
        $this->storage = $storage;
        $this->size = $size;
        $this->length = $length;
        $this->addressLength = ceil(log10($size));
        $this->fullLength = $this->length + $this->addressLength;
    }

    public function hashWithModifiers($c1, $c2)
    {
        $this->c1 = $c1;

        $this->c2 = $c2;
    }

    public function insert($file)
    {
        $file = $this->fix($file, $this->length);

        $node = $this->findNode($file, $index);

        if($node === null && $index < $this->size) {

            return $this->write($index, $file);

        }

        if($node !== null) {

            $current = $this->value($node);

            return $this->write($index, $file, $current + 1);

        }

        throw new Exception('Storage is full!');
    }

    public function search($file)
    {
        step(); $file = $this->fix($file, $this->length);

        step(); return $this->findNode($file);
    }

    private function findNode($file, &$index = null)
    {
        for(step(), $i = 0; step(), $i < $this->size; step(), $i++) {

            step(); $index = ($this->hash($file) + $this->c1 * $i + $this->c2 * $i * $i) % $this->size;

            step(); $node = $this->read($index);

            step(); $key = $this->key($node);

            step(); if($key == '') return null;

            step(); if($key == trim($file)) return $node;

        }

        step(); return null;
    }

    private function read($address)
    {
        step(); $position = $address * $this->fullLength;

        step(); return $this->storage->read($position, $this->fullLength);
    }

    private function write($address, $key, $value = 1)
    {
        $position = $address * $this->fullLength;

        $key = $this->fix($key, $this->length);

        $value = $this->fix($value, $this->addressLength);

        $this->storage->write($position, $key . $value);
    }

    public function key($node)
    {
        step(); $result = substr($node, 0, $this->length);

        step(); return trim($result);
    }

    public function value($node)
    {
        step(); $result = substr($node, $this->length, $this->addressLength);

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

        for(step(), $i = 0; step(), $i < 3; step(), $i++) {

            step(); $char = @ord($name[$i]);

            step(); $result += $char * pow(256, $i);

        }

        step(); return $result % $this->size;
    }
}