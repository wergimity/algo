<?php
namespace App\Structures;

use App\FileStorage;
use Exception;

class RedBlackSearchTree
{
    const RED = 1;

    const BLACK = 0;

    const UPDATE_LEFT = 2;

    const UPDATE_RIGHT = 3;

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

    /**
     * @var int
     */
    private $topAddress;

    public function __construct(FileStorage $storage, $size, $length)
    {
        $this->storage = $storage;
        $this->size = $size;
        $this->length = $length;
        $this->nextAddress = 0;
        $this->addressLength = ceil(log10($size));
        $this->fullLength = $this->length + $this->addressLength*4 + 1;
        $this->topAddress = 0;
    }

    public function insert($file)
    {
        $file = $this->fix($file, $this->length);

        $node = $this->findNode($file, $parent, $side, $address);

        if($node !== null) {

            $value = $this->value($node) + 1;

            return $this->updateValue($address, $value);

        }

        $address = $this->release($file, $parent, $side);

        return $this->balance($address);
    }

    public function search($file)
    {
        step(); $file = $this->fix($file, $this->length);

        step(); return $this->findNode($file);
    }

    public function fixTop()
    {
        for($address = 0; !$this->storage->ends(); $address++) {

            $node = $this->read($address);

            $parent = $this->parent($node);

            if($parent < 0) {

                $this->topAddress = $address;

                return true;

            }

        }

        return false;
    }

    private function balance($address)
    {
        $node = $this->read($address);

        $parentAddress = $this->parent($node);

        // Case 1
        if($parentAddress < 0) return $this->updateColor($address, self::BLACK);

        $parent = $this->read($parentAddress);

        // Case 2
        if($this->color($parent) == self::BLACK) return null;

        $uncleAddress = $this->uncle($node, $grandAddress);

        $uncle = $uncleAddress === null ? '' : $this->read($uncleAddress);

        $grandparent = $grandAddress >= 0 ? $this->read($grandAddress) : '';

        // Case 3
        if(trim($uncle) && $this->color($uncle) == self::RED) {

            $this->updateColor($parentAddress, self::BLACK);

            $this->updateColor($uncleAddress, self::BLACK);

            $this->updateColor($grandAddress, self::RED);

            return $this->balance($grandAddress);

        }

        // case 4

        if($this->right($parent) == $address && $this->left($grandparent) == $this->parent($node)) {

            $this->rotateLeft($address);

            $node = $this->read($address);

            $address = $this->left($node);

        } else if($this->left($parent) == $address && $this->right($grandparent) == $this->parent($node)) {

            $this->rotateRight($address);

            $node = $this->read($address);

            $address = $this->right($node);
        }

        // case 5
        return $this->finalRotation($address);
    }

    public function rotateLeft($address)
    {
        $node = $this->read($address);

        $parent = $this->parent($node);

        $grandpa = $this->grandpa($node);

        $left = $this->left($node);

        $this->updateRight($parent, $left);

        if($left !== null) $this->updateParent($left, $parent);

        $this->updateLeft($address, $parent);

        $this->replaceChild($grandpa, $parent, $address);
    }

    public function rotateRight($address)
    {
        $node = $this->read($address);

        $parent = $this->parent($node);

        $grandpa = $this->grandpa($node);

        $right = $this->right($node);

        $this->updateLeft($parent, $right);

        if($right !== null) $this->updateParent($right, $parent);

        $this->updateRight($address, $parent);

        $this->replaceChild($grandpa, $parent, $address);
    }

    private function finalRotation($address)
    {
        $node = $this->read($address);

        $parentAddress = $this->parent($node);

        $parent = $this->read($parentAddress);

        $grandAddress = $this->parent($parent);

        if($this->left($parent) == $address) {

            $this->rotateRight($parentAddress);

        } else {

            $this->rotateLeft($parentAddress);

        }

        $this->updateColor($parentAddress, self::BLACK);

        $this->updateColor($grandAddress, self::RED);
    }

    public function replaceChild($address, $child, $newChild)
    {
        $node = $this->read($address);

        $left = $this->left($node);

        $this->updateParent($child, $newChild);

        $this->updateParent($newChild, $address);

        if($address < 0) return null;

        if($left == $child) return $this->updateLeft($address, $newChild);

        return $this->updateRight($address, $newChild);
    }

    private function findNode($file, &$parent = null, &$side = null, &$address = null)
    {
        step(); $file = trim($file);

        step(); $parent = -1;

        step(); $current = $this->topAddress;

        step(); $side = null;

        do {

            step(); $node = $this->read($current);

            step(); $key = $this->key($node);

            step(); $left = $this->left($node);

            step(); $right = $this->right($node);

            step(); if(!$key || $key == $file) break;

            step(); if($key > $file) {

                step(); $parent = $current;

                step(); $current = $left;

                step(); $side = self::UPDATE_LEFT;

            } else {

                step(); $parent = $current;

                step(); $current = $right;

                step(); $side = self::UPDATE_RIGHT;

            }

        } while(trim($node) && $current !== null && step());

        step(); if($key == $file) {

        step(); $address = $current;

        step(); return $node;

        }

        step(); return null;
    }

    public function release($file, $parent, $side)
    {
        $address = $this->nextAddress++;

        if($address >= $this->size) {
            throw new Exception('Storage is full!');
        }

        $position = $address * $this->fullLength;

        $node = $this->node($file, 1, self::RED, $parent);

        $this->storage->write($position, $node);

        if($side == self::UPDATE_LEFT) {

            $this->updateLeft($parent, $address);

        }

        if($side == self::UPDATE_RIGHT) {

            $this->updateRight($parent, $address);

        }

        return $address;
    }

    public function read($address)
    {
        step(); if ($address === null) {
        step(); return '';
        }

        step(); $position = $address * $this->fullLength;

        step(); $length = $this->fullLength;

        step(); return $this->storage->read($position, $length);
    }

    private function node($key, $value, $color, $parent, $left = null, $right = null)
    {
        $result = $this->fix($key, $this->length);

        $result .= $this->fix($value, $this->addressLength);

        $result .= substr($color, 0, 1);

        $result .= $this->fix($parent, $this->addressLength);

        $result .= $this->fix($left, $this->addressLength);

        $result .= $this->fix($right, $this->addressLength);

        return $result;
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

    public function color($node)
    {
        $position = $this->length + $this->addressLength;

        return (int) substr($node, $position, 1);
    }

    public function parent($node)
    {
        $position = $this->fullLength - $this->addressLength * 3;

        return (int) substr($node, $position, $this->addressLength);
    }

    public function left($node)
    {
        step(); $position = $this->fullLength - $this->addressLength * 2;

        step(); $result = substr($node, $position, $this->addressLength);

        step(); if(trim($result) == '') return null;

        step(); return (int) $result;
    }

    public function right($node)
    {
        step(); $position = $this->fullLength - $this->addressLength;

        step(); $result = substr($node, $position, $this->addressLength);

        step(); if(trim($result) == '') return null;

        step(); return (int) $result;
    }

    public function uncle($node, &$grandAddress = null)
    {
        $parent = $this->parent($node);

        $grandAddress = $this->grandpa($node);

        if($grandAddress < 0) return null;

        $grandparent = $this->read($grandAddress);

        if($parent == $this->left($grandparent)) return $this->right($grandparent);

        if($parent == $this->right($grandparent)) return $this->left($grandparent);

        return null;
    }

    public function grandpa($node)
    {
        $parentAddress = $this->parent($node);

        if($parentAddress < 0) return null;

        $parent = $this->read($parentAddress);

        $grandAddress = $this->parent($parent);

        return $grandAddress;
    }

    private function updateValue($address, $value)
    {
        $position = $address * $this->fullLength + $this->length;

        $value = $this->fix($value, $this->addressLength);

        $this->storage->write($position, $value);
    }

    private function updateLeft($address, $value)
    {
        $position = ($address + 1) * $this->fullLength - $this->addressLength * 2;

        $value = $this->fix($value, $this->addressLength);

        $this->storage->write($position, $value);
    }

    private function updateRight($address, $value)
    {
        $position = ($address + 1) * $this->fullLength - $this->addressLength;

        $value = $this->fix($value, $this->addressLength);

        $this->storage->write($position, $value);
    }

    private function updateColor($address, $value)
    {
        $position = $address * $this->fullLength + $this->length + $this->addressLength;

        $value = substr($value, 0, 1);

        $this->storage->write($position, $value);
    }

    private function updateParent($address, $value)
    {
        $position = ($address + 1) * $this->fullLength - $this->addressLength * 3;

        if($value < 0) $this->topAddress = $address;

        $value = $this->fix($value, $this->addressLength);

        $this->storage->write($position, $value);
    }

    private function fix($value, $length)
    {
        step(); return str_pad(substr($value, 0, $length), $length, ' ');
    }

}