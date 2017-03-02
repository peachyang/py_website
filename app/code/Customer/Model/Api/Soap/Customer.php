<?php

namespace Seahinet\Customer\Model\Api\Soap;

use Seahinet\Api\Model\Api\AbstractHandler;
use Seahinet\Customer\Model\Customer as Model;
use Seahinet\Lib\Model\Collection\Eav\Attribute;

class Customer extends AbstractHandler
{

    /**
     * @param string $sessionId
     * @param string $username
     * @param string $password
     * @return int
     */
    public function customerValid($sessionId, $username, $password)
    {
        $this->validateSessionId($sessionId);
        $customer = new Model;
        return $customer->valid($username, $this->decryptData($password)) ? $customer->getId() : 0;
    }

    /**
     * @param string $sessionId
     * @param int $customerId
     * @return array
     */
    public function customerInfo($sessionId, $customerId)
    {
        $this->validateSessionId($sessionId);
        $customer = new Model;
        $customer->load($customerId);
        $result = ['id' => $customer->getId()];
        $attributes = new Attribute;
        $attributes->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                ->where(['eav_entity_type.code' => Model::ENTITY_TYPE])
        ->where->notEqualTo('input', 'password');
        $attributes->load(true, true);
        $attributes->walk(function($attribute) use (&$result, $customer) {
            $result[$attribute['code']] = $customer->offsetGet($attribute['code']);
        });
        return $this->response($result);
    }
/**
     * @param string $sessionId
     * @param int $customerId
     * @return array
     */
    public function customerRegister($sessionId,$data)
    {
        $config = $this->getContainer()->get('config');
        $this->validateSessionId($sessionId);
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
            //$result = $this->validateForm($data, $required, in_array('register', $config['customer/captcha/form']) ? 'customer' : false);
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
}
