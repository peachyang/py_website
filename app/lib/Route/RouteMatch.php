<?php

namespace Seahinet\Lib\Route;

use Seahinet\Lib\Http\Request;

class RouteMatch
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
        return $this->request? : new Request;
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

}
