<?php

namespace Seahinet\Lib\Controller;

use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Http\Response;
use Seahinet\Lib\Route\RouteMatch;

class ActionController
{

    use \Seahinet\Lib\Traits\Container;
    
    /**
     * @var Request 
     */
    protected $request = null;

    /**
     * @var Response 
     */
    protected $response = null;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param Request $request
     * @param RouteMatch $routeMatch
     * @return Response|null|string
     */
    public function dispatch($request = null, $routeMatch = null)
    {
        $this->request = $request;
        if (!$routeMatch instanceof RouteMatch) {
            $method = 'notFoundAction';
        } else {
            $method = $routeMatch->getMethod();
            $this->options = $routeMatch->getOptions();
            if (!is_callable([$this, $method])) {
                $method = 'notFoundAction';
            }
        }
        return $this->$method();
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        if (is_null($this->request)) {
            $this->request = $this->getContainer()->get('request');
        }
        return $this->request;
    }

    /**
     * @return Response
     */
    protected function getResponse()
    {
        if (is_null($this->response)) {
            $this->response = $this->getContainer()->get('response');
        }
        return $this->response;
    }

    public function notFoundAction()
    {
        return $this->getResponse()->withStatus(404);
    }

    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

}
