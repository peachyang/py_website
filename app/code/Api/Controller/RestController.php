<?php

namespace Seahinet\Api\Controller;

use Exception;
use Seahinet\Oauth\Model\Token;
use Seahinet\Customer\Model\Collection\Customer as CustomerCollection;
use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\Controller\ApiActionController;
use Seahinet\Lib\Model\Collection\Eav\Attribute;

class RestController extends ApiActionController
{

    public function __call($name, $arguments)
    {
        if (is_callable([$this, $method = $this->getRequest()->getMethod() . rtrim($name, 'Action')])) {
            return $this->$method();
        }
        return $this->getResponse()->withStatus(400);
    }

    protected function getAttributes($type, $isRead = true)
    {
        $attributes = new Attribute;
        $attributes->columns(['code'])
                ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                ->join('api_rest_attribute', 'api_rest_attribute.attribute_id=eav_attribute.id', [], 'left')
                ->join('api_rest_role', 'api_rest_role.id=api_rest_attribute.role_id', [], 'left')
                ->where([
                    ($isRead ? 'readable' : 'writeable') => 1,
                    'role_id' => $this->authOptions['role_id'],
                    'eav_entity_type.code' => $type
        ]);
        return $attributes;
    }

    protected function getCustomer()
    {
        $data = $this->getRequest()->getQuery();
        if ($this->authOptions['role_id'] == -1) {
            $attributes = $this->getAttributes();
            if (isset($data['id'])) {
                $customer = new Customer;
                $customer->load($data['id']);
                $result = [];
                foreach ($attributes as $attribute) {
                    $result[$attribute['code']] = $customer[$attribute['code']];
                }
                return $result;
            } else {
                $collection = new CustomerCollection;
                $result = [];
                foreach ($collection as $item) {
                    $result[$item['id']] = [];
                    foreach ($attributes as $attribute) {
                        $result[$item['id']][$attribute['code']] = $item[$attribute['code']];
                    }
                }
                return $result;
            }
        } else if (isset($data['openId']) && $data['openId'] === $this->authOptions['open_id']) {
            $token = new Token;
            $token->load($data['openId'], 'open_id');
            $customer = new Customer;
            $customer->load($token['customer_id']);
            $attributes = $this->getAttributes('customer');
            $result = [];
            foreach ($attributes as $attribute) {
                if (isset($customer[$attribute['code']])) {
                    $result[$attribute['code']] = $customer[$attribute['code']];
                }
            }
            return $result;
        }
        return $this->getResponse()->withStatus(400);
    }

    protected function deleteCustomer()
    {
        if ($this->authOptions['role_id'] === -1 &&
                count($this->getAttributes('customer', false))) {
            $id = $this->getRequest()->getQuery('id');
            if ($id) {
                $customer = new Customer;
                try {
                    $customer->setId($id)->remove();
                    return $this->getResponse()->withStatus(202);
                } catch (Exception $e) {
                    return $this->getResponse()->withStatus(400);
                }
            }
            return $this->getResponse()->withStatus(400);
        }
        return $this->getResponse()->withStatus(403);
    }

    protected function putCustomer()
    {
        if ($this->authOptions['role_id'] === -1) {
            $id = $this->getRequest()->getQuery('id');
            $customer = new Customer;
            if ($id) {
                $customer->load($id);
            }
            $data = $this->getRequest()->getPost();
            $attributes = $this->getAttributes('customer', false);
            $set = [];
            foreach ($attributes as $attribute) {
                if (isset($data[$attribute['code']])) {
                    $set[$attribute['code']] = $data[$attribute['code']];
                }
            }
            try {
                if ($set) {
                    $customer->setData($set);
                    $customer->save();
                }
                return $this->getResponse()->withStatus(202);
            } catch (Exception $e) {
                return $this->getResponse()->withStatus(400);
            }
        }
        return $this->getResponse()->withStatus(403);
    }

}
