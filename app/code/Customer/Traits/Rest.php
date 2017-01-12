<?php

namespace Seahinet\Customer\Traits;

use Exception;
use Seahinet\Oauth\Model\Token;
use Seahinet\Customer\Model\Collection\{
    Address as AddressCollection,
    Customer as CustomerCollection
};
use Seahinet\Customer\Model\{
    Address,
    Customer
};

trait Rest
{

    protected function getCustomer()
    {
        $data = $this->getRequest()->getQuery();
        $attributes = $this->getAttributes(Customer::ENTITY_TYPE);
        if ($this->authOptions['validation'] == -1) {
            $collection = new CustomerCollection;
            $collection->columns($attributes);
            $this->filter($collection, $data);
            return $collection->load(true, true)->toArray();
        } else if (isset($data['openId']) && $data['openId'] === $this->authOptions['open_id']) {
            $token = new Token;
            $token->load($data['openId'], 'open_id');
            $customer = new Customer;
            $customer->load($token['customer_id']);
            $result = [];
            foreach ($attributes as $attribute) {
                if (isset($customer[$attribute])) {
                    $result[$attribute] = $customer[$attribute];
                }
            }
            return $result;
        }
        return $this->getResponse()->withStatus(403);
    }

    protected function deleteCustomer()
    {
        if ($this->authOptions['validation'] === -1 &&
                count($this->getAttributes(Customer::ENTITY_TYPE, false))) {
            $id = $this->getRequest()->getQuery('id');
            if ($id) {
                $customer = new Customer;
                $customer->setId($id)->remove();
                return $this->getResponse()->withStatus(202);
            }
            return $this->getResponse()->withStatus(400);
        }
        return $this->getResponse()->withStatus(403);
    }

    protected function putCustomer()
    {
        $attributes = $this->getAttributes(Customer::ENTITY_TYPE, false);
        $data = $this->getRequest()->getPost();
        $set = [];
        foreach ($attributes as $attribute) {
            if (isset($data[$attribute])) {
                $set[$attribute] = $data[$attribute];
            }
        }
        if ($set) {
            if ($this->authOptions['validation'] === -1) {
                $id = $data['id'];
            } else if (isset($data['openId']) && $data['openId'] === $this->authOptions['open_id']) {
                $token = new Token;
                $token->load($data['openId'], 'open_id');
                $id = $token['customer_id'];
            }
            $customer = new Customer;
            $customer->load($id);
            $customer->setData($set);
            $customer->save();
            return $this->getResponse()->withStatus(202);
        }
        return $this->getResponse()->withStatus(403);
    }

    protected function getAddress()
    {
        $data = $this->getRequest()->getQuery();
        $attributes = $this->getAttributes(Address::ENTITY_TYPE);
        if ($this->authOptions['validation'] == -1) {
            $collection = new AddressCollection;
            $collection->columns($attributes);
            $this->filter($collection, $data);
            return $collection->load(true, true)->toArray();
        } else if (isset($data['openId']) && $data['openId'] === $this->authOptions['open_id']) {
            $token = new Token;
            $token->load($data['openId'], 'open_id');
            $collection = new AddressCollection;
            $collection->columns($attributes)
                    ->where(['customer_id' => $token['customer_id']]);
            return $collection->load(true, true)->toArray();
        }
        return $this->getResponse()->withStatus(403);
    }

    protected function deleteAddress()
    {
        if (count($this->getAttributes(Address::ENTITY_TYPE, false))) {
            $data = $this->getRequest()->getQuery();
            if ($this->authOptions['validation'] === -1) {
                if ($data['id']) {
                    $address = new Address;
                    $address->setId($id)->remove();
                    return $this->getResponse()->withStatus(202);
                }
                return $this->getResponse()->withStatus(400);
            } else if (isset($data['openId']) && $data['openId'] === $this->authOptions['open_id']) {
                if ($data['id']) {
                    $address = new Address;
                    $address->load($data['id']);
                    $token = new Token;
                    $token->load($data['openId'], 'open_id');
                    if ($address['customer_id'] == $token['customer_id']) {
                        $address->remove();
                        return $this->getResponse()->withStatus(202);
                    }
                }
                return $this->getResponse()->withStatus(400);
            }
        }
        return $this->getResponse()->withStatus(403);
    }

    public function putAddress()
    {
        $attributes = $this->getAttributes(Address::ENTITY_TYPE, false);
        $data = $this->getRequest()->getPost();
        $set = [];
        foreach ($attributes as $attribute) {
            if (isset($data[$attribute])) {
                $set[$attribute] = $data[$attribute];
            }
        }
        if ($set) {
            $id = $data['id'];
            $address = new Address;
            $address->load($id);
            if ($this->authOptions['validation'] > 0) {
                $flag = false;
                if (isset($data['openId']) && $data['openId'] === $this->authOptions['open_id']) {
                    $token = new Token;
                    $token->load($data['openId'], 'open_id');
                    if ($address['customer_id'] == $token['customer_id']) {
                        $flag = true;
                    }
                }
                if ($flag) {
                    throw new Exception('Not Allowed');
                }
            }
            $address->setData($set);
            $address->save();
            return $this->getResponse()->withStatus(202);
        }
        return $this->getResponse()->withStatus(403);
    }

}
