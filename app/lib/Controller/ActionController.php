<?php

namespace Seahinet\Lib\Controller;

use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Http\Response;
use Seahinet\Lib\Route\RouteMatch;
use Seahinet\Lib\Session\Csrf;

abstract class ActionController
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
     * @var Csrf 
     */
    protected $csrf = null;

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

    /**
     * Redirect to referer location
     * 
     * @param string $location If there is no referer header
     * @param string $code HTTP Status Code 301|302
     * @return Response
     */
    protected function redirectReferer($location = '/', $code = 302)
    {
        $referer = $this->getRequest()->getHeader('Referer');
        return $this->getResponse()->withHeader('Location', $referer? : $location)->withStatus($code);
    }

    /**
     * Redirect to the specified location
     * 
     * @param string $location
     * @param string $code HTTP Status Code 301|302
     * @return Response
     */
    protected function redirect($location = '/', $code = 302)
    {
        return $this->getResponse()->withHeader('Location', $location)->withStatus($code);
    }

    /**
     * Forward to the specified path with re-route
     * 
     * @param string $path
     * @return null
     */
    protected function forward($path = '/')
    {
        $this->getRequest()->getUri()->withPath($path);
        $this->getContainer()->get('eventDispatcher')->trigger('route', ['routers' => $this->getContainer()->get('config')['route']]);
        return null;
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
     * Validate csrf key for forms
     * 
     * @param string $value
     * @return string
     */
    protected function validateFormKey($value)
    {
        if (is_null($this->csrf)) {
            $this->csrf = new Csrf;
        }
        return $this->csrf->isValid($value);
    }

    protected function translate()
    {
        
    }

}
