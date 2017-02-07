<?php

namespace Seahinet\Api\Model\Api;

use SoapFault;
use Seahinet\Api\Model\Soap\User;
use Seahinet\Api\Model\Soap\Session;

final class General extends AbstractHandler
{

    /**
     * @param string $username
     * @param string $password
     * @return string
     */
    public function login($username, $password)
    {
        $user = new User;
        $user->load($username, 'username');
        if ($user->getId() && $user->valid($username, $this->decryptData($password, $user))) {
            $session = new Session;
            $session->setData('user_id', $user->getId())
                    ->save();
            return $session->getId();
        }
        return new SoapFault('Client', 'Invalid username or password.');
    }

    /**
     * @param string $sessionId
     * @return bool
     */
    public function endSession($sessionId)
    {
        $this->validateSessionId($sessionId);
        $this->session->remove();
        return true;
    }

}
