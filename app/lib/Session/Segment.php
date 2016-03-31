<?php

namespace Seahinet\Lib\Session;

use ArrayAccess;
use IteratorAggregate;
use Seahinet\Lib\Session;

/**
 * @todo Get/Set values in different domains
 */
class Segment implements IteratorAggregate, ArrayAccess
{

    /**
     * Session object
     * @var Session 
     */
    protected $session = null;

    /**
     * Segment identify
     * @var string 
     */
    protected $name = '';

    /**
     * @var array
     */
    protected $iterator = [];

    public function __construct($name, Session $session = null)
    {
        $this->name = $name;
        if (is_null($session)) {
            $session = Session::instance();
        }
        $this->session = $session;
        if (!$this->session->sessionExists()) {
            $this->session->start();
        }
        if (!isset($_SESSION[$this->name])) {
            $_SESSION[$this->name] = [];
        } else {
            $this->iterator = $_SESSION[$this->name];
        }
    }

    public function get($key)
    {
        return isset($this->iterator[$key]) ? $this->iterator[$key] : '';
    }

    public function set($key, $value)
    {
        if ($value instanceof \Closure) {
            $value = $value();
        }
        $this->iterator[$key] = $value;
        $_SESSION[$this->name][$key] = serialize($value);
        return $this;
    }

    public function getIterator()
    {
        return $this->iterator;
    }

    public function offsetExists($key)
    {
        return isset($this->iterator[$key]);
    }

    public function offsetGet($key)
    {
        return $this->get($key);
    }

    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    public function offsetUnset($key)
    {
        unset($this->iterator[$key]);
        unset($_SESSION[$this->name][$key]);
    }

    public function addMessage(array $message)
    {
        if (!isset($this->iterator['message'])) {
            $messages = [];
        } else {
            $messages = $this->iterator['message'] + $message;
        }
        $this->set('message', $messages);
    }

    public function getMessage()
    {
        $result = $this->get('message');
        $this->offsetUnset('message');
        return $result;
    }

    public function clear()
    {
        $this->iterator = [];
        $_SESSION[$this->name] = [];
    }

}
