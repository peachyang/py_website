<?php

namespace Seahinet\Lib\Controller;

use Seahinet\Lib\Route\RouteMatch;
use Seahinet\Lib\Session\Segment;

/**
 * Controller with authorization for backend pages
 */
class AuthActionController extends ActionController
{

    public function dispatch($request = null, $routeMatch = null)
    {
        $this->request = $request;
        if (!$routeMatch instanceof RouteMatch) {
            $method = 'notFoundAction';
        } else {
            $method = $routeMatch->getMethod();
            $this->options = $routeMatch->getOptions();
            $segment = new Segment('admin');
            $permission = str_replace('Seahinet\\', '', preg_replace('/Controller(?:\\\\)?/', '', get_class($this))) . '::' . str_replace('Action', '', $method);
            if (!$segment->get('isLoggedin') || !$segment->get('user')->getRole()->hasPermission($permission)) {
                return $this->notFoundAction();
            }
            if (!is_callable([$this, $method])) {
                $method = 'notFoundAction';
            }
        }
        return $this->$method();
    }

    protected function response($result, $url)
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($result['error'] && isset($result['error_url'])) {
                $result['redirect'] = $result['error_url'];
            } else if (!$result['error'] && isset($result['success_url'])) {
                $result['redirect'] = $result['success_url'];
            }
            return $result;
        } else {
            $this->addMessage($result['message'], 'danger', 'admin');
            if ($result['error']) {
                return $this->redirectReferer($url);
            }
            return $this->redirect($url);
        }
    }

    protected function validateForm(array $data, array $required = [], $captcha = false)
    {
        $result = ['error' => 0, 'message' => []];
        if (!isset($data['csrf']) || !$this->validateCsrfKey($data['csrf'])) {
            $result['message'][] = ['message' => $this->translate('The form submitted did not originate from the expected site.'), 'level' => 'danger'];
            $result['error'] = 1;
        }
        foreach ($required as $item) {
            if (empty($data[$item])) {
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
