<?php

namespace Seahinet\Lib\Controller;

use FastRoute\Dispatcher;
use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Http\Response;
use Seahinet\Lib\Route\RouteMatch;

class ActionController
{

    /**
     * @var Request 
     */
    protected $request = null;

    /**
     * @var Response 
     */
    protected $response = null;

    /**
     * @param Request $request
     * @param RouteMatch $routeMatch
     * @return Response|null|string
     */
    public function dispatch($request = null, $routeMatch = null)
    {
        if (!$routeMatch) {
            $method = 'notFoundAction';
        }
        $this->request = $request;
        $method = $routeMatch->getMethod();
        if (!is_callable([$this, $method])) {
            $method = 'notFoundAction';
        }
        return $this->$method();
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        if (is_null($this->request)) {
            $this->request = new Request;
        }
        return $this->request;
    }

    /**
     * @return Response
     */
    protected function getResponse()
    {
        if (is_null($this->response)) {
            $this->response = new Response;
        }
        return $this->response;
    }

    public function notFoundAction()
    {
        
    }

}
