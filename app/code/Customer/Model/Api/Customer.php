<?php

namespace Seahinet\Customer\Model\Api;

use Seahinet\Api\Model\Api\AbstractHandler;
use Seahinet\Customer\Model\Customer as Model;

class Customer extends AbstractHandler
{

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $username;

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
        return $this->response($customer->toArray());
    }

}
