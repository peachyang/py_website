<?php

namespace Seahinet\Lib\Translator;

use ArrayAccess;
use Closure;
use JsonSerializable;
use Serializable;

/**
 * Save splid translatation pairs
 */
class Category implements ArrayAccess, JsonSerializable, Serializable
{

    protected $storage;

    public function __construct($input = [])
    {
        $this->storage = $input;
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    public function __unset($name)
    {
        $this->offsetUnset($name);
    }

    public function offsetExists($offset)
    {
        return isset($this->storage[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->storage[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (!is_string($value)) {
            if ($value instanceof Closure) {
                $value = $value($this);
            } else {
                $value = serialize($value);
            }
        }
        $this->storage[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->storage[$offset]);
    }

    public function serialize()
    {
        return serialize($this->storage);
    }

    public function unserialize($serialized)
    {
        $this->storage = unserialize($serialized);
    }

    public function jsonSerialize()
    {
        return json_encode($this->storage);
    }

    public function getArrayCopy()
    {
        return $this->storage;
    }

    public function merge(...$arrays)
    {
        foreach ($arrays as $array) {
            if (is_object($array) && is_callable([$array, 'getArrayCopy'])) {
                $array = $array->getArrayCopy();
            }
            $this->storage += $array;
        }
        return $this;
    }

}
