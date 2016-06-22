<?php

namespace Seahinet\Lib\Controller;

use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Http\Response;
use Seahinet\Lib\Route\RouteMatch;

/**
 * Abstract controller
 */
abstract class AbstractController
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
     * Dispath a request
     * 
     * @param Request $request
     * @param RouteMatch $routeMatch
     * @return mixed
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
        return $this->doDispatch($method);
    }

    /**
     * Do dispatch
     * 
     * @param string $method
     * @return mixed
     */
    protected function doDispatch($method = 'notFoundAction')
    {
        if ($method !== 'notFoundAction') {
            $param = ['controller' => $this, 'method' => $method];
            $dispatcher = $this->getContainer()->get('eventDispatcher');
            $dispatcher->trigger(get_class($this) . '.dispatch.before', $param);
            $dispatcher->trigger('dispatch.before', $param);
        }
        $result = $this->$method();
        if ($method !== 'notFoundAction') {
            $param = ['controller' => $this, 'method' => $method, 'result' => &$result];
            $dispatcher = $this->getContainer()->get('eventDispatcher');
            $dispatcher->trigger(get_class($this) . '.dispatch.after', $param);
            $dispatcher->trigger('dispatch.after', $param);
        }
        return $result;
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

    /**
     * @param string $name
     * @return mixed
     */
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return ActionController
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }

}
