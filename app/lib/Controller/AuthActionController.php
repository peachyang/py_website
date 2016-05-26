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
        if ($method !== 'notFoundAction') {
            $param = ['controller' => $this, 'method' => $method];
            $dispatcher = $this->getContainer()->get('eventDispatcher');
            $dispatcher->trigger(get_class($this) . '.dispatch.before', $param);
            $dispatcher->trigger('auth.dispatch.before', $param);
            $dispatcher->trigger('dispatch.before', $param);
        }
        return $this->$method();
    }

}
