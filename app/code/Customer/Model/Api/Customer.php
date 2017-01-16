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
     * @var string
     */
    public $email;

    /**
     * @var boolean
     */
    public $gender;

    /**
     * @var string
     */
    public $avatar;

    /**
     * @var int
     */
    public $rewardpoints;

    /**
     * @var int
     */
    public $balance;

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
