<?php

namespace Seahinet\Api\Model\Api;

use SoapFault;
use Seahinet\Api\Model\Soap\{
    Session,
    User
};
use Zend\Crypt\PublicKey\{
    Rsa,
    Rsa\PrivateKey
};

class AbstractHandler implements HandlerInterface
{

    protected $user = [];
    protected $session;

    /**
     * @param string $sessionId
     * @return boolean
     * @throws SoapFault
     */
    protected function validateSessionId($sessionId)
    {
        if ($sessionId) {
            $session = new Session;
            $session->load($sessionId);
            if ($session->getId()) {
                if (strtotime($session->offsetGet('log_date')) < time() - 3600) {
                    $session->remove();
                } else {
                    $this->session = $session;
                    $this->user = new User;
                    $this->user->load($session->offsetGet('user_id'));
                    return true;
                }
            }
        }
        throw new SoapFault('Client', 'Unknown session id: ' . $sessionId);
    }

    protected function encryptData($data, $user = null)
    {
        if (is_null($user)) {
            $user = $this->user;
        }
        if (!empty($user['private_key'])) {
            $rsa = new Rsa;
            return $rsa->encrypt($data, new PrivateKey($user->offsetGet('private_key'), $user->offsetGet('phrase')));
        }
        return $data;
    }

    protected function decryptData($data, $user = null)
    {
        if (is_null($user)) {
            $user = $this->user;
        }
        if (!empty($user['private_key'])) {
            $rsa = new Rsa;
            return $rsa->decrypt($data, new PrivateKey($user->offsetGet('private_key'), $user->offsetGet('phrase')));
        }
        return $data;
    }

}
