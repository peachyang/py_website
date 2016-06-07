<?php

namespace Seahinet\CUstomer\Controller;

use Gregwar\Captcha\PhraseBuilder;
use Gregwar\Captcha\CaptchaBuilder;
use Seahinet\Customer\Model\Collection\Customer as Collection;
use Seahinet\Customer\Model\Customer as Model;
use Seahinet\Email\Model\Template;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Session\Segment;
use Swift_SwiftException;
use Zend\Math\Rand;

class AccountController extends ActionController
{

    protected static $allowedAction = [
        'create', 'login', 'createpost', 'loginpost', 'forgotpwd', 'forgotpwdpost', 'captcha'
    ];

    public function dispatch($request = null, $routeMatch = null)
    {
        $options = $routeMatch->getOptions();
        $action = isset($options['action']) ? strtolower($options['action']) : 'index';
        $session = new Segment('customer');
        if (!in_array($action, static::$allowedAction) && !$session->get('isLoggedin')) {
            return $this->redirect('customer/account/login/');
        } else if (in_array($action, static::$allowedAction) && $session->get('isLoggedin')) {
            return $this->redirect('customer/account/');
        }
        return parent::dispatch($request, $routeMatch);
    }

    public function createAction()
    {
        return $this->getLayout('customer_account_create');
    }

    public function loginAction()
    {
        return $this->getLayout('customer_account_login');
    }

    public function forgotPwdAction()
    {
        return $this->getLayout('customer_account_forgotpwd');
    }

    public function captchaAction()
    {
        $config = $this->getContainer()->get('config');
        $builder = new CaptchaBuilder(null, new PhraseBuilder($config['customer/captcha/number'], $config['customer/captcha/symbol']));
        $builder->setBackgroundColor(0xff, 0xff, 0xff);
        $builder->build(70, 26);
        $segment = new Segment('customer');
        $segment->set('captcha', strtoupper($builder->getPhrase()));
        $this->getResponse()
                ->withHeader('Content-type', 'image/jpeg')
                ->withHeader('Cache-Control', 'no-store');
        return $builder->get();
    }

    public function createPostAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $attributes = new Attribute;
            $attributes->withSet()->where(['attribute_set_id' => 1])
                    ->where('is_required=1 OR is_unique=1')
                    ->columns(['code', 'is_required', 'is_unique', 'type_id'])
                    ->join('eav_entity_type', 'eav_attribute.type_id=eav_entity_type.id', [], 'right')
                    ->where(['eav_entity_type.code' => Model::ENTITY_TYPE]);
            $required = [];
            $unique = [];
            foreach ($attributes as $attribute) {
                if ($attribute['is_required']) {
                    $required[] = $attribute['code'];
                }
                if ($attribute['is_unique']) {
                    $unique[] = $attribute['code'];
                }
            }
            $config = $this->getContainer()->get('config');
            $result = $this->validateForm($data, $required, in_array('register', explode(',', $config['customer/captcha/form'])) ? 'customer' : false);
            if ($data['password'] !== $data['cpassword']) {
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('The comfirmed password is not equal to the password.'), 'level' => 'danger'];
            }
            $collection = new Collection;
            $collection->columns($unique);
            foreach ($unique as $code) {
                if (isset($data[$code])) {
                    $collection->where([$code => $data[$code]], 'OR');
                }
            }
            if (count($collection)) {
                foreach ($collection as $item) {
                    foreach ($unique as $code) {
                        if (isset($item[$code]) && $item[$code]) {
                            $result['error'] = 1;
                            $result['message'][] = ['message' => $this->translate('The field %s has been used.', [$code]), 'level' => 'danger'];
                        }
                    }
                    break;
                }
            }
            if ($result['error'] === 0) {
                $customer = new Model;
                $customer->setData([
                    'attribute_set_id' => $config['customer/registion/set'],
                    'group_id' => $config['customer/registion/group'],
                    'type_id' => $attributes[0]['type_id'],
                    'store_id' => Bootstrap::getStore()->getId(),
                    'language_id' => Bootstrap::getLanguage()->getId(),
                    'status' => (int) (!$config['customer/registion/confirm'])
                        ] + $data)->save();
                $customer->login($data['username'], $data['password']);
            }
        }
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], 'customer/account/');
    }

    public function loginPostAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $config = $this->getContainer()->get('config');
            $segment = new Segment('customer');
            $result = $this->validateForm($data, ['username', 'password'], (in_array('login', explode(',', $config['customer/captcha/form'])) && ($config['customer/captcha/mode'] == 0 || $config['customer/captcha/attempt'] <= $segment->get('fail2login'))) ? 'customer' : false);
            if ($result['error'] == 0) {
                $customer = new Model;
                if ($customer->login($data['username'], $data['password'])) {
                    $result['data'] = ['username' => $data['username']];
                    $result['message'][] = ['message' => $this->translate('Welcome %s.', [$customer['username']], 'customer'), 'level' => 'success'];
                } else if ($customer['status']) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('Invalid username or password.'), 'level' => 'danger'];
                } else {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('This account is not confirmed.'), 'level' => 'danger'];
                }
            }
            if ($result['error']) {
                $segment->set('fail2login', (int) $segment->get('fail2login') + 1);
            } else {
                $segment->set('fail2login', 0);
            }
        }
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], 'customer/account/');
    }

    public function forgotPwdPostAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['username'], in_array('forgotpwd', explode(',', $this->getContainer()->get('config')['customer/captcha/form'])) ? 'customer' : false);
            if ($result['error'] === 0) {
                $customer = new Model;
                $customer->load($data['username'], 'username');
                $password = Rand::getString(8);
                try {
                    $mailer = $this->getContainer()->get('mailer');
                    $mailer->send((new Template)->load('forgot_password', 'code')->getMessage(['{{password}}' => $password])->addFrom('idriszhang@seahinet.com')->addTo($customer->offsetGet('email'), $customer->offsetGet('username')));
                    $customer->setData('password', $password)->save();
                    $result['message'][] = ['message' => $this->translate('You will receive an email with a temporary password.'), 'level' => 'success'];
                } catch (Swift_SwiftException $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected while email transporting. Please try again later.'), 'level' => 'danger'];
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected. Please try again later.'), 'level' => 'danger'];
                }
            }
        }
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], 'customer/account/login/');
    }

    public function logoutAction()
    {
        $segment = new Segment('customer');
        $segment->offsetUnset('customer');
        $segment->set('isLoggedin', false);
        $result = ['error' => 0, 'message' => [[
            'message' => $this->translate('You have logged out successfully.'),
            'level' => 'success'
        ]]];
        return $this->response($result, 'customer/account/login/');
    }

}
