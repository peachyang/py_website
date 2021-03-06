<?php

namespace Seahinet\Customer\Controller;

use Exception;
use Gregwar\Captcha\PhraseBuilder;
use Gregwar\Captcha\CaptchaBuilder;
use Seahinet\Customer\Model\Address;
use Seahinet\Customer\Model\Collection\Customer as Collection;
use Seahinet\Customer\Model\Customer as Model;
use Seahinet\Customer\Model\Persistent;
use Seahinet\Email\Model\Template as TemplateModel;
use Seahinet\Email\Model\Collection\Template as TemplateCollection;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Session\Segment;
use Swift_TransportException;
use Zend\Db\Sql\Where;
use Zend\Math\Rand;

class AccountController extends AuthActionController
{

    use \Seahinet\Lib\Traits\DB;

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
        $builder->build(105, 39);
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
            $attributes->withSet()->where(['attribute_set_id' => $config['customer/registion/set']])
                    ->where('(is_required=1 OR is_unique=1)')
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
                $result['message'][] = ['message' => $this->translate('The confirmed password is not equal to the password.'), 'level' => 'danger'];
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
                $status = $config['customer/registion/confirm'];
                $languageId = Bootstrap::getLanguage()->getId();
                $customer->setData([
                    'id' => null,
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
                        $result['data'] = ['id' => $customer['id'], 'username' => $data['username'], 'email' => $customer['email']];
                        $url = 'customer/account/';
                        $result['message'][] = ['message' => $this->translate('Thanks for your registion.'), 'level' => 'success'];
                        $this->useSso($result);
                    }
                    if (!empty($data['subscribe'])) {
                        $this->getContainer()->get('eventDispatcher')->trigger('subscribe', ['data' => $data]);
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
                                        ->addFrom($config['email/customer/sender_email'] ?: $config['email/default/sender_email'], $config['email/customer/sender_name'] ?: $config['email/default/sender_name'])
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
        return $this->response($result ?? ['error' => 0, 'message' => []], $url ?? '/customer/account/create/', 'customer');
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
                    $result['success_url'] = $data['success_url'] ?? '';
                    if (!empty($data['persistent'])) {
                        $persistent = new Persistent;
                        $key = md5(random_bytes(32) . $data['username']);
                        $persistent->setData([
                            'customer_id' => $customer->getId(),
                            'key' => $key
                        ])->save();
                        $result['cookie'] = ['key' => 'persistent', 'value' => $key, 'path' => '/', 'expires' => time() + 604800];
                    }
                    $result['data'] = ['id' => $customer['id'], 'username' => $data['username'], 'email' => $customer['email']];
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
                $this->useSso($result);
                $segment->set('fail2login', 0);
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], $url ?? 'customer/account/login/', 'customer');
    }

    public function forgotPwdPostAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['username'], in_array('forgotpwd', $this->getContainer()->get('config')['customer/captcha/form']) ? 'customer' : false);
            if ($result['error'] === 0) {
                $segment = new Segment('customer');
                $customer = new Model;
                $customer->load($data['username'], 'username');
                if (!$customer->getId()) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('Invalid username.'), 'level' => 'danger'];
                    return $this->response($result, 'customer/account/login/', 'customer');
                }
                $key = $segment->get('reset_password');
                if (empty($key) || $key['time'] < strtotime('-1hour')) {
                    $password = Rand::getString(8);
                    $segment->set('reset_password', [
                        'key' => $password,
                        'time' => time()
                    ]);
                } else {
                    $password = $key['key'];
                }
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
                                        ->addFrom($config['email/customer/sender_email'] ?: $config['email/default/sender_email'], $config['email/customer/sender_name'] ?: $config['email/default/sender_name'])
                                        ->addTo($customer->offsetGet('email'), $customer->offsetGet('username')));
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
        return $this->response($result ?? ['error' => 0, 'message' => []], 'customer/account/login/', 'customer');
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
        if ($url = $this->getRequest()->getQuery('success_url')) {
            $result['success_url'] = base64_decode($url);
        }
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
                                            ->addFrom($config['email/customer/sender_email'] ?: $config['email/default/sender_email'], $config['email/customer/sender_name'] ?: $config['email/default/sender_name'])
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
                                            ->addFrom($config['email/customer/sender_email'] ?: $config['email/default/sender_email'], $config['email/customer/sender_name'] ?: $config['email/default/sender_name'])
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
        return $this->response($result ?? ['error' => 0, 'message' => []], 'customer/account/login/', 'customer');
    }

    public function indexAction()
    {
        return $this->getLayout('customer_account_dashboard');
    }

    public function editAction()
    {
        return $this->getLayout('customer_account_edit');
    }

    public function saveAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $segment = new Segment('customer');
            $customer = $segment->get('customer');
            $attributes = new Attribute;
            $attributes->withSet()->where([
                        'is_unique' => 1,
                        'attribute_set_id' => $data['attribute_set_id'] ?? $customer['attribute_set_id']
                    ])->columns(['code'])
                    ->join('eav_entity_type', 'eav_attribute.type_id=eav_entity_type.id', [], 'right')
                    ->where(['eav_entity_type.code' => Model::ENTITY_TYPE])
            ->where->notEqualTo('input', 'password');
            $unique = [];
            $attributes->walk(function ($attribute) use (&$unique) {
                $unique[] = $attribute['code'];
            });
            if (!$customer->valid($customer['username'], $data['crpassword'])) {
                $result['message'][] = ['message' => $this->translate('The current password is incorrect.'), 'level' => 'danger'];
                $result['error'] = 1;
            }
            if ($unique) {
                $collection = new Collection;
                $collection->columns($unique);
                $where = new Where;
                $flag = false;
                foreach ($unique as $code) {
                    if (isset($data[$code])) {
                        $predicate = new Where;
                        $predicate->equalTo($code, $data[$code]);
                        $where->orPredicate($predicate);
                        $flag = true;
                    }
                }
                $collection->getSelect()->where->notEqualTo('id', $customer['id'])->andPredicate($where);
                if ($flag && count($collection)) {
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
            }
            $result = $this->validateForm($data, ['crpassword']);
            if (!empty($data['edit_password'])) {
                if (empty($data['cpassword']) || empty($data['password']) || $data['cpassword'] !== $data['password']) {
                    $result['message'][] = ['message' => $this->translate('The confirm password is not equal to the password.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
                $data['modified_password'] = 1;
            } else {
                unset($data['cpassword'], $data['password']);
            }
            if ($result['error'] === 0) {
                try {
                    $files = $this->getRequest()->getUploadedFile();
                    foreach ($files as $key => $file) {
                        if ($file->getError() == 0) {
                            if (!is_dir(BP . 'pub/upload/customer/' . $key)) {
                                mkdir(BP . 'pub/upload/customer/' . $key, 0777, true);
                            }
                            $name = $customer['id'] . substr($file->getClientFilename(), strpos($file->getClientFilename(), '.'));
                            $path = BP . 'pub/upload/customer/' . $key . '/' . $name;
                            if (file_exists($path)) {
                                unlink($path);
                            }
                            $file->moveTo($path);
                            $data[$key] = $name;
                        } else {
                            unset($data[$key]);
                        }
                    }
                    $model = new Model;
                    $model->load($customer['id']);
                    $model->setData($data);
                    $this->getContainer()->get('eventDispatcher')->trigger('frontend.customer.save.before', ['model' => $model, 'data' => $data]);
                    $model->save();
                    $this->getContainer()->get('eventDispatcher')->trigger('frontend.customer.save.after', ['model' => $model, 'data' => $data]);
                    $segment->set('customer', clone $model);
                    $result['message'][] = ['message' => $this->translate('An item has been saved successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected while saving.'), 'level' => 'success'];
                }
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], 'customer/account/edit/', 'customer');
    }

    public function addressAction()
    {
        return $this->getLayout('customer_account_address');
    }

    public function deleteAddressAction()
    {
        if ($this->getRequest()->isDelete()) {
            $address = new Address;
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['id']);
            if ($result['error'] === 0) {
                try {
                    $address->setId($data['id'])->remove();
                    $result['removeLine'] = 1;
                    $result['message'][] = ['message' => $this->translate('The address has been deleted successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected while deleting. Please contact us or try again later.'), 'level' => 'success'];
                }
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], 'customer/account/address/', 'customer');
    }

    public function saveAddressAction()
    {
        $result = ['error' => 0, 'message' => []];
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
                try {
                    $segment = new Segment('customer');
                    if (isset($data['id'])) {
                        $address->load($data['id']);
                    }
                    if (!empty($address->offsetGet('customer_id')) && $address->offsetGet('customer_id') != $segment->get('customer')->getId()) {
                        throw new Exception('');
                    }
                    $address->setData($data + [
                        'attribute_set_id' => $setId,
                        'store_id' => Bootstrap::getStore()->getId(),
                        'customer_id' => $segment->get('hasLoggedIn') ? $segment->get('customer')->getId() : null
                    ])->save();
                    $result['message'][] = ['message' => $this->translate('The address has been saved successfully.'), 'level' => 'success'];
                    $result['data'] = ['id' => $address->getId(), 'content' => $address->display()];
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected while saving. Please contact us or try again later.'), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result, 'customer/account/address', 'customer');
    }

    public function defaultAddressAction()
    {
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $address = new Address;
            $address->load($id)->setData('is_default', 1)->save();
        }
        return $this->response(['error' => 0, 'message' => []], 'customer/account/address/');
    }

    public function logviewAction()
    {
        $root = $this->getLayout('customer_account_logview');
        return $root;
    }

}
