<?php

namespace Seahinet\Lib\Route;

use ArrayAccess;
use Seahinet\Lib\Http\Request;

/**
 * Signed a success route path
 */
class RouteMatch implements ArrayAccess
{

    /**
     * @var Request 
     */
    protected $request = null;

    /**
     * @var array 
     */
    protected $options = null;

    /**
     * @param array $options
     * @param Request $request
     */
    public function __construct($options = [], $request = null)
    {
        $this->options = $options;
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request ?: new Request;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return isset($this->options['action']) && $this->options['action'] ? $this->options['action'] . 'Action' : 'indexAction';
    }

    public function offsetExists($offset)
    {
        return isset($this->options[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->options[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->options[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->options[$offset]);
    }

}
