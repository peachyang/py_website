<?php

namespace Seahinet\Lib\Cache;

use Psr\Cache\CacheItemInterface;

class Item implements CacheItemInterface
{

    protected $key = '';
    protected $value = null;
    protected $lifetime = 0;

    public function __construct($key, $value, $lifetime = 0)
    {
        $this->key = $key;
        $this->value = $value;
        $this->lifetime = $lifetime;
    }

    public function expiresAfter($time = 0)
    {
        $this->lifetime = $time;
        return $this->lifetime;
    }

    public function expiresAt($expiration = 0)
    {
        if ($expiration < time()) {
            $expiration = 1;
        }
        $this->lifetime = $expiration;
        return $this;
    }

    public function get()
    {
        return $this->isHit() ? $this->value : null;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function isHit()
    {
        return true;
    }

    public function set($value)
    {
        $this->value = $value;
        return $this;
    }

}
