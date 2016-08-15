<?php

namespace Seahinet\Customer\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;

abstract class AuthActionController extends ActionController
{

    protected $allowedAction = ['login', 'loginpost', 'forgotpwd', 'forgotpwdpost', 'captcha', 'confirm'];

    public function __construct()
    {
        if ($this->getContainer()->get('config')['customer/registion/enabled']) {
            $this->allowedAction = array_merge($this->allowedAction, ['create', 'createpost']);
        }
    }

    public function dispatch($request = null, $routeMatch = null)
    {
        $options = $routeMatch->getOptions();
        $action = isset($options['action']) ? strtolower($options['action']) : 'index';
        $session = new Segment('customer');
        if (!in_array($action, $this->allowedAction) && !$session->get('hasLoggedIn')) {
            return $this->redirect('customer/account/login/');
        } else if (in_array($action, $this->allowedAction) && $session->get('hasLoggedIn')) {
            return $this->redirect('customer/account/');
        }
        return parent::dispatch($request, $routeMatch);
    }

}
