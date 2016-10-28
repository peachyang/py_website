<?php

namespace Seahinet\Lib\Http;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

/**
 * Collection of HTTP headers used in both the HTTP request and response objects.
 * 
 * @see https://github.com/slimphp/Slim/blob/3.x/Slim/Http/Headers.php
 */
class Headers implements ArrayAccess, IteratorAggregate
{

    protected $headers = [];
    protected static $special = [
        'CONTENT_TYPE' => 1,
        'CONTENT_LENGTH' => 1,
        'PHP_AUTH_USER' => 1,
        'PHP_AUTH_PW' => 1,
        'PHP_AUTH_DIGEST' => 1,
        'AUTH_TYPE' => 1,
    ];

    public function __construct(array $data = [])
    {
        $this->headers = $data;
    }

    public static function createFromEnvironment(array $server)
    {
        $data = [];
        foreach ($server as $key => $value) {
            $key = strtoupper($key);
            if (isset(static::$special[$key]) || strpos($key, 'HTTP_') === 0) {
                if ($key !== 'HTTP_CONTENT_LENGTH') {
                    $data[$key] = $value;
                }
            }
        }

        return new static($data);
    }

    public function offsetExists($offset)
    {
        return isset($this->headers[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->headers[$offset] ?? ($this->headers['HTTP_' . $offset] ?? '');
    }

    public function offsetSet($offset, $value)
    {
        $this->headers[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->headers[$offset]);
    }

    public function __toString()
    {
        $str = '';
        foreach ($this->headers as $key => $value) {
            $str .= ($str === '' ? '' : '\r\n') . $key . ': ' . $value;
        }
        return $str;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->headers);
    }

}
