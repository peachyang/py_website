<?php

namespace Seahinet\Lib\Controller;

use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Http\Response;
use Seahinet\Lib\Route\RouteMatch;
use Seahinet\Lib\Session\Csrf;
use Seahinet\Lib\Session\Segment;

/**
 * Controller for normal pages
 */
abstract class ActionController
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\Translate,
        \Seahinet\Lib\Traits\Url;

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
        $referer = $this->getRequest()->getHeader('HTTP_REFERER');
        if (!$referer && strpos($location, '://') === false) {
            $location = strpos($location, ':ADMIN') === false ? $this->getBaseUrl($location) : $this->getAdminUrl($location);
        }
        return $this->redirect($referer? : $location, $code);
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
        if (strpos($location, '://') === false) {
            $location = strpos($location, ':ADMIN') === false ? $this->getBaseUrl($location) : $this->getAdminUrl($location);
        }
        return $this->getResponse()->withHeader('Location', $location)->withStatus($code);
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

    /**
     * Validate csrf key for forms
     * 
     * @param string $value
     * @return string
     */
    protected function validateCsrfKey($value)
    {
        if (is_null($this->csrf)) {
            $this->csrf = new Csrf;
        }
        return $this->csrf->isValid($value);
    }

    /**
     * Validate captcha value for forms
     * 
     * @param string $value
     * @param Segment $segment
     * @return boolean
     */
    protected function validateCaptcha($value, $segment = 'core')
    {
        $segment = new Segment($segment);
        return $segment->get('captcha') == strtoupper($value);
    }

    /**
     * Add message to session
     * 
     * @param string|array $message
     * @param Segment|string $segment
     */
    protected function addMessage($message, $level = 'info', $segment = 'core')
    {
        if (!$message) {
            return;
        }
        if (is_string($segment)) {
            $segment = new Segment($segment);
        }
        $segment->addMessage(is_string($message) ? [['message' => $message, 'level' => $level]] : $message);
    }

    /**
     * Get message from session
     * 
     * @param Segment|string $segment
     * @return array
     */
    protected function getMessage($segment = 'core')
    {
        if (is_string($segment)) {
            $segment = new Segment($segment);
        }
        return (array) $segment->getMessage();
    }

    /**
     * Get layout
     * 
     * @param string $handler
     * @param bool $render
     * @return \Seahinet\Lib\ViewModel\Root|array
     */
    protected function getLayout($handler, $render = true)
    {
        return $this->getContainer()->get('layout')->getLayout($handler, $render);
    }

    /**
     * Response in different way
     * 
     * @param array $result
     * @param string $url
     * @return Response|array
     */
    protected function response($result, $url, $segment = 'admin')
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($result['error'] && isset($result['error_url'])) {
                $result['redirect'] = $result['error_url'];
            } else if (!$result['error'] && isset($result['success_url'])) {
                $result['redirect'] = $result['success_url'];
            }
            return $result;
        } else {
            $this->addMessage($result['message'], 'danger', $segment);
            if ($result['error']) {
                return $this->redirectReferer($url);
            }
            return $this->redirect(isset($result['success_url']) ? $result['success_url'] : $url);
        }
    }

    /**
     * Validate form data
     * 
     * @param array $data
     * @param array $required
     * @param bool $captcha
     * @return array
     */
    protected function validateForm(array $data, array $required = [], $captcha = false)
    {
        $result = ['error' => 0, 'message' => []];
        if (!isset($data['csrf']) || !$this->validateCsrfKey($data['csrf'])) {
            $result['message'][] = ['message' => $this->translate('The form submitted did not originate from the expected site.'), 'level' => 'danger'];
            $result['error'] = 1;
        }
        foreach ($required as $item) {
            if (!isset($data[$item]) || !is_numeric($data[$item]) && empty($data[$item])) {
                $result['message'][] = ['message' => $this->translate('The ' . $item . ' field is required and can not be empty.'), 'level' => 'danger'];
                $result['error'] = 1;
            }
        }
        if ($captcha && (empty($data['captcha']) || !$this->validateCaptcha($data['captcha'], $captcha))) {
            $result['message'][] = ['message' => $this->translate('The captcha value is wrong.'), 'level' => 'danger'];
            $result['error'] = 1;
        }
        if (isset($data['success_url'])) {
            $result['success_url'] = rawurldecode($data['success_url']);
        }
        if (isset($data['error_url'])) {
            $result['error_url'] = rawurldecode($data['error_url']);
        }
        return $result;
    }

}
