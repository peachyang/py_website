<?php

namespace Seahinet\Customer\Controller;

use Exception;
use Gregwar\Captcha\PhraseBuilder;
use Gregwar\Captcha\CaptchaBuilder;
use Seahinet\Customer\Model\Collection\Customer as Collection;
use Seahinet\Customer\Model\Customer as Model;
use Seahinet\Email\Model\Template as TemplateModel;
use Seahinet\Email\Model\Collection\Template as TemplateCollection;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Session\Segment;
use Seahinet\Customer\Model\Address;
use Seahinet\Customer\Model\Collection\Address as Addresses;
use Seahinet\Customer\Model\Collection\Wishlist as Wishlists;
use Seahinet\Customer\Model\Persistent;
use Seahinet\Customer\Model\Wishlist\Item;
use Swift_TransportException;
use Zend\Math\Rand;

class AccountController extends AuthActionController
{

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
        $config = $this->getContainer()->get('config');
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
            $result = $this->validateForm($data, $required, in_array('register', $config['customer/captcha/form']) ? 'customer' : false);
            if (!isset($data['cpassword']) || $data['password'] !== $data['cpassword']) {
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
                $status = $config['customer/registion/comfirm'];
                $languageId = Bootstrap::getLanguage()->getId();
                $customer->setData([
                    'attribute_set_id' => $config['customer/registion/set'],
                    'group_id' => $config['customer/registion/group'],
                    'type_id' => $attributes[0]['type_id'],
                    'store_id' => Bootstrap::getStore()->getId(),
                    'language_id' => $languageId,
                    'status' => 1
                        ] + $data);
                $token = Rand::getString(32);
                try {
                    if ($status) {
                        $customer->setData([
                            'confirm_token' => $token,
                            'confirm_token_created_at' => date('Y-m-d H:i:s'),
                            'status' => 0
                        ])->save();
                        $url = 'customer/account/login/';
                        $result['message'][] = ['message' => $this->translate('You will receive an email with a confirming link.'), 'level' => 'success'];
                    } else {
                        $customer->save();
                        $customer->login($data['username'], $data['password']);
                        $url = 'customer/account/';
                        $result['message'][] = ['message' => $this->translate('Thanks for your registion.'), 'level' => 'success'];
                    }
                    $collection = new TemplateCollection;
                    $collection->join('email_template_language', 'email_template_language.template_id=email_template.id', [], 'left')
                            ->where([
                                'code' => $status ? $config['email/customer/confirm_template'] : $config['email/customer/welcome_template'],
                                'language_id' => $languageId
                    ]);
                    if (count($collection)) {
                        $mailer = $this->getContainer()->get('mailer');
                        $mailer->send((new TemplateModel($collection[0]))
                                        ->getMessage(['username' => $data['username'], 'confirm' => $this->getBaseUrl('customer/account/confirm/?token=' . $token)])
                                        ->addFrom($config['email/customer/sender_email']? : $config['email/default/sender_email'], $config['email/customer/sender_name']? : $config['email/default/sender_name'])
                                        ->addTo($data['email'], $data['username']));
                    }
                } catch (Swift_TransportException $e) {
                    $this->getContainer()->get('log')->logException($e);
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected. Please try again later.'), 'level' => 'danger'];
                }
            }
        }
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], isset($url) ? $url : '/customer/account/create/', 'customer');
    }

    public function loginPostAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $config = $this->getContainer()->get('config');
            $segment = new Segment('customer');
            $result = $this->validateForm($data, ['username', 'password'], (in_array('login', $config['customer/captcha/form']) && ($config['customer/captcha/mode'] == 0 || $config['customer/captcha/attempt'] <= $segment->get('fail2login'))) ? 'customer' : false);
            if ($result['error'] == 0) {
                $customer = new Model;
                if ($customer->login($data['username'], $data['password'])) {
                    $url = 'customer/account/';
                    if (!empty($data['persistent'])) {
                        $persistent = new Persistent;
                        $key = md5(random_bytes(32) . $data['username']);
                        $persistent->setData([
                            'customer_id' => $customer->getId(),
                            'key' => $key
                        ])->save();
                        $result['cookie'] = ['key' => 'persistent', 'value' => $key, 'path' => '/', 'expires' => time() + 604800];
                    }
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
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], isset($url) ? $url : 'customer/account/login/', 'customer');
    }

    public function forgotPwdPostAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['username'], in_array('forgotpwd', $this->getContainer()->get('config')['customer/captcha/form']) ? 'customer' : false);
            if ($result['error'] === 0) {
                $customer = new Model;
                $customer->load($data['username'], 'username');
                $password = Rand::getString(8);
                try {
                    $config = $this->getContainer()->get('config');
                    $collection = new TemplateCollection;
                    $collection->join('email_template_language', 'email_template_language.template_id=email_template.id', [], 'left')
                            ->where([
                                'code' => $config['email/customer/forgot_template'],
                                'language_id' => $customer['language_id']
                    ]);
                    if (count($collection)) {
                        $mailer = $this->getContainer()->get('mailer');
                        $mailer->send((new TemplateModel($collection[0]))
                                        ->getMessage(['username' => $data['username'], 'password' => $password])
                                        ->addFrom($config['email/customer/sender_email']? : $config['email/default/sender_email'], $config['email/customer/sender_name']? : $config['email/default/sender_name'])
                                        ->addTo($customer->offsetGet('email'), $customer->offsetGet('username')));
                        $customer->setData('password', $password)->save();
                    }
                    $result['message'][] = ['message' => $this->translate('You will receive an email with a temporary password.'), 'level' => 'success'];
                } catch (Swift_TransportException $e) {
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
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], 'customer/account/login/', 'customer');
    }

    public function logoutAction()
    {
        $segment = new Segment('customer');
        $segment->offsetUnset('customer');
        $segment->set('hasLoggedIn', false);
        $result = ['error' => 0, 'message' => [[
            'message' => $this->translate('You have logged out successfully.'),
            'level' => 'success',
            'cookie' => ['key' => 'persistent', 'value' => null]
        ]]];
        $this->getContainer()->get('eventDispatcher')->trigger('customer.logout.after');
        return $this->response($result, 'customer/account/login/', 'customer');
    }

    public function confirmAction()
    {
        if ($token = $this->getRequest()->getQuery('token')) {
            try {
                $customer = new Model;
                $customer->load($token, 'confirm_token');
                if ($customer->getId() && $customer['status'] == 0) {
                    $mailer = $this->getContainer()->get('mailer');
                    $languageId = Bootstrap::getLanguage()->getId();
                    $config = $this->getContainer()->get('config');
                    if (strtotime($customer['confirm_token_created_at']) < time() + 86400) {
                        $customer->setData([
                            'status' => 1,
                            'confirm_token' => null,
                            'confirm_token_created_at' => null
                        ])->save();
                        $result = ['error' => 0, 'message' => [[
                            'message' => $this->translate('Your account has been confirmed successfully.'),
                            'level' => 'success'
                        ]]];
                        $collection = new TemplateCollection;
                        $collection->join('email_template_language', 'email_template_language.template_id=email_template.id', [], 'left')
                                ->where([
                                    'code' => $config['email/customer/welcome_template'],
                                    'language_id' => $languageId
                        ]);
                        if (count($collection)) {
                            $mailer->send((new TemplateModel($collection[0]))
                                            ->getMessage(['{{username}}' => $customer['username'], '{{confirm}}' => $this->getBaseUrl('customer/account/confirm/?token=' . $token)])
                                            ->addFrom($config['email/customer/sender_email']? : $config['email/default/sender_email'], $config['email/customer/sender_name']? : $config['email/default/sender_name'])
                                            ->addTo($customer['email'], $customer['username']));
                        }
                    } else {
                        $token = Rand::getString(32);
                        $collection = new TemplateCollection;
                        $collection->join('email_template_language', 'email_template_language.template_id=email_template.id', [], 'left')
                                ->where([
                                    'code' => $config['email/customer/confirm_template'],
                                    'language_id' => $languageId
                        ]);
                        if (count($collection)) {
                            $mailer->send((new TemplateModel($collection[0]))
                                            ->getMessage(['{{username}}' => $customer['username'], '{{confirm}}' => $this->getBaseUrl('customer/account/confirm/?token=' . $token)])
                                            ->addFrom($config['email/customer/sender_email']? : $config['email/default/sender_email'], $config['email/customer/sender_name']? : $config['email/default/sender_name'])
                                            ->addTo($customer['email'], $customer['username']));
                            $customer->setData([
                                'confirm_token' => $token,
                                'confirm_token_created_at' => date('Y-m-d H:i:s')
                            ])->save();
                        }
                        $result = ['error' => 0, 'message' => [[
                            'message' => $this->translate('The confirming link is expired.'),
                            'level' => 'danger'
                        ]]];
                    }
                }
            } catch (Swift_TransportException $e) {
                $this->getContainer()->get('log')->logException($e);
            } catch (Exception $e) {
                $this->getContainer()->get('log')->logException($e);
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('An error detected. Please try again later.'), 'level' => 'danger'];
            }
        }
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], 'customer/account/login/', 'customer');
    }

    public function indexAction()
    {
        return $this->getLayout('customer_account_dashboard');
    }

    public function personalInfoAction()
    {
        $segment = new Segment('customer');
        $customerId = $segment->get('customer')->getId();
        $customer = new Model;
        $customer->load($customerId);
        $root = $this->getLayout('customer_account_personalinfo');
        $root->getChild('main', true)->setVariable('customer', $customer);
        return $root;
    }

    public function editPersonalInfoAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $segment = new Segment('customer');
            $customer = $segment->get('customer');
            $result = $this->validateForm($data, ['crpassword', 'password']);
            if (empty($data['cpassword']) || empty($data['password']) || $data['cpassword'] !== $data['password']) {
                $result['message'][] = ['message' => $this->translate('The confirm password is not equal to the password.'), 'level' => 'danger'];
                $result['error'] = 1;
                $url = 'customer/account/personalInfo/';
            } else if (!$customer->valid($customer['username'], $data['crpassword'])) {
                $result['message'][] = ['message' => $this->translate('The current password is incorrect.'), 'level' => 'danger'];
                $result['error'] = 1;
                $url = 'customer/account/personalInfo/';
            } else if ($result['error'] === 0) {
                $model = new Model;
                $model->load($customer['id']);
                $model->setData($data);
                $model->save();
                if (isset($data['id']) && $data['id'] == $customer->getId()) {
                    $customer->setData($data);
                    $segment->set('customer', clone $customer);
                }
                $result['message'][] = ['message' => $this->translate('An item has been saved successfully.'), 'level' => 'success'];
                $url = 'customer/account/';
            }
        }
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], $url, 'customer');
    }

    public function addressAction()
    {
        $segment = new Segment('customer');
        $customerId = $segment->get('customer')->getId();
        $addresses = new Addresses;
        $addresses->where(['customer_id' => $customerId]);
        $root = $this->getLayout('customer_account_address');
        $root->getChild('main', true)->setVariable('addresses', $addresses);
        return $root;
    }

    public function delAddressAction()
    {
        $address = new Address;
        $data = $this->getRequest()->getQuery();
        $address->load($data['id'])->remove();

        return $this->redirect('customer/account/address/');
    }

    public function addAddressAction()
    {
        $result = ['error' => 1, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $attribute = new Attribute;
            $attribute->withSet()
                    ->columns(['code'])
                    ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [])
                    ->where(['eav_entity_type.code' => Address::ENTITY_TYPE, 'is_required' => 1]);
            $required = [];
            $setId = $attribute[0]['attribute_set_id'];
            $attribute->walk(function($item) use (&$required) {
                $required[] = $item['code'];
            });
            $result = $this->validateForm($data, $required);
            if ($result['error'] === 0) {
                $address = new Address;
                unset($data['customer_id']);
                unset($data['store_id']);
                unset($data['attribute_set_id']);
                try {
                    $segment = new Segment('customer');
                    $address->setData($data + [
                        'attribute_set_id' => $setId,
                        'store_id' => Bootstrap::getStore()->getId(),
                        'customer_id' => $segment->get('hasLoggedIn') ? $segment->get('customer')->getId() : null
                    ])->save();
                    $result['data'] = ['id' => $address->getId(), 'content' => $address->display()];
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected while saving. Please contact us or try again later.'), 'level' => 'danger'];
                }
            }
        }
        return $this->redirect('customer/account/address');
    }

    public function defaultAddressAction()
    {
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $address = new Address;
            $address->load($id)->setData('is_default', 1)->save();
            $collection = new Addresses;
            $collection->where(['is_default' => 1])->where->notEqualTo('id', $id);
            foreach ($collection as $address) {
                $address->setData('is_default', 0)->save();
            }
        }
        return $this->redirect('customer/account/address/');
    }

    public function wishlistAction()
    {
        $segment = new Segment('customer');
        $customerId = $segment->get('customer')->getId();
        $wishlists = new Wishlists;
        $wishlists->where(['customer_id' => $customerId]);
        $root = $this->getLayout('customer_account_wishlist');
        $root->getChild('main', true)->setVariable('wishlists', $wishlists);
        return $root;
    }

    public function deleteAction()
    {
        $item = new Item;
        $data = $this->getRequest()->getQuery();
        if (isset($data['id'])) {
            $item->load($data['id'])->remove();
        }
        return $this->redirect('customer/account/wishlist/');
    }

    public function logviewAction()
    {
        $segment = new Segment('logview');
        $root = $this->getLayout('customer_account_logview');
        return $root;
    }

}
