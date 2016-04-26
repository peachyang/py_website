<?php

namespace Seahinet\Lib\Stdlib;

use ArrayAccess;
use Serializable;

/**
 * Simplify PHP ArrayObject
 * It is not Traversable
 */
class ArrayObject implements ArrayAccess, Serializable
{

    /**
     * @var array
     */
    protected $storage;

    /**
     * Returns the value at the specified key by reference
     *
     * @param  mixed $key
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->offsetGet($key);
    }

    /**
     * Returns whether the requested key exists
     *
     * @param  mixed $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Sets the value at the specified key to value
     *
     * @param  mixed $key
     * @param  mixed $value
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Unsets the value at the specified key
     *
     * @param  mixed $key
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Returns whether the requested key exists
     *
     * @param  mixed $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->storage[$key]);
    }

    /**
     * Returns the value at the specified key
     *
     * @param  mixed $key
     * @return mixed
     */
    public function &offsetGet($key)
    {
        $ret = null;
        if (!$this->offsetExists($key)) {
            return $ret;
        }
        $ret = & $this->storage[$key];

        return $ret;
    }

    /**
     * Sets the value at the specified key to value
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->storage[$key] = $value;
    }

    /**
     * Unsets the value at the specified key
     *
     * @param  mixed $key
     * @return void
     */
    public function offsetUnset($key)
    {
        if ($this->offsetExists($key)) {
            unset($this->storage[$key]);
        }
    }

    /**
     * Serialize an ArrayObject
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->storage);
    }

    /**
     * Unserialize an ArrayObject
     *
     * @param  string $data
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->storage = unserialize($serialized);
        if ($this instanceof Singleton) {
            static::$instance = $this;
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->storage;
    }

}