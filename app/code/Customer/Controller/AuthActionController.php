<?php

namespace Seahinet\Customer\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;
use Zend\Crypt\{
    BlockCipher,
    Symmetric\Openssl
};

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
            if ($url = $this->getRequest()->getQuery('success_url')) {
                $data['success_url'] = base64_decode($url);
                $customer = $session->get('customer');
                $data['data'] = ['id' => $customer['id'], 'username' => $customer['username'], 'email' => $customer['email']];
                if ($this->useSso($data)) {
                    return $this->redirect($data['success_url']);
                }
            }
            return $this->redirect('customer/account/');
        }
        return parent::dispatch($request, $routeMatch);
    }

    protected function useSso(&$result)
    {
        $config = $this->getContainer()->get('config');
        if ($config['customer/login/sso'] && !empty($result['success_url']) && $config['customer/login/allowed_sso_url'] && in_array(parse_url($result['success_url'], PHP_URL_HOST), explode(';', $config['customer/login/allowed_sso_url']))) {
            $result['message'] = [];
            $cipher = new BlockCipher(new Openssl);
            $cipher->setKey($config['customer/login/sso_key']);
            $result['success_url'] .= '?token=' . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($cipher->encrypt(json_encode($result['data']))));
            return true;
        }
        return false;
    }

}
