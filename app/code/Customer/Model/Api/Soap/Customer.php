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

}
