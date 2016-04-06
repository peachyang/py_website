<?php

namespace Seahinet\Lib\Controller;

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
            if (!$segment->get('isLoggedin') || !$segment->get('user')->getRole()->hasPermission(get_class($this) . '::' . $method)) {
                return $this->notFoundAction();
            }
            if (!is_callable([$this, $method])) {
                $method = 'notFoundAction';
            }
        }
        return $this->$method();
    }

    protected function redirectReferer($location = '/', $code = 302)
    {
        return parent::redirectReferer(str_replace(':ADMIN', 'admin', $location), $code);
    }

    protected function redirect($location = '/', $code = 302)
    {
        return parent::redirect(str_replace(':ADMIN', 'admin', $location), $code);
    }

    protected function forward($path = '/')
    {
        return parent::forward(str_replace(':ADMIN', 'admin', $location));
    }

}
