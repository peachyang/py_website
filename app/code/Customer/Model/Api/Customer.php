<?php

namespace Seahinet\Customer\Model\Api;

use Seahinet\Api\Model\Api\AbstractHandler;
use Seahinet\Customer\Model\Customer as Model;

class Customer extends AbstractHandler
{

    /**
     * @param string $sessionId
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function customerValid($sessionId, $username, $password)
    {
        $this->validateSessionId($sessionId);
        $customer = new Model;
        return $customer->valid($username, $this->decryptData($password));
    }

}
