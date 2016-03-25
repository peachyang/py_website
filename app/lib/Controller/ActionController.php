<?php

namespace ActionController;

use FastRoute\Dispatcher;
use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Http\Response;

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
     * @param array $routeMatch
     * @param Request $request
     * @return Response|null|string
     */
    public function dispatch($routeMatch, $request = null)
    {
        if ($routeMatch[0] == Dispatcher::NOT_FOUND) {
            $method = 'notFoundAction';
        } else if ($routeMatch[0] == Dispatcher::METHOD_NOT_ALLOWED) {
            $method = 'notAllowedAction';
        }
        $this->request = $request;
        if (isset($routeMatch[2]['action'])) {
            $method = $routeMatch[2]['action'] . 'Action';
        } else {
            $method = 'indexAction';
        }
        if (!is_callable($this, $method)) {
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

    public function notAllowedAction()
    {
        
    }

}
