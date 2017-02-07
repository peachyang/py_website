<?php

namespace Seahinet\Api\Model\Api;

use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;
use Seahinet\Api\Model\Soap\{
    Session,
    User
};
use SoapFault;
use Zend\Crypt\PublicKey\{
    Rsa,
    RsaOptions,
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

    /**
     * @param string $data
     * @param User $user
     * @return string
     */
    protected function encryptData($data, $user = null)
    {
        if (is_null($user)) {
            $user = $this->user;
        }
        if (!empty($user['private_key'])) {
            $rsa = new Rsa((new RsaOptions)->setOpensslPadding(OPENSSL_PKCS1_PADDING));
            return $rsa->encrypt($data, new PrivateKey($user->offsetGet('private_key'), $user->offsetGet('phrase')));
        }
        return $data;
    }

    /**
     * @param string $data
     * @param User $user
     * @return string
     */
    protected function decryptData($data, $user = null)
    {
        if (is_null($user)) {
            $user = $this->user;
        }
        if (!empty($user['private_key'])) {
            $rsa = new Rsa((new RsaOptions)->setOpensslPadding(OPENSSL_PKCS1_PADDING));
            return $rsa->decrypt($data, new PrivateKey($user->offsetGet('private_key'), $user->offsetGet('phrase')), Rsa::MODE_BASE64);
        }
        return $data;
    }

    /**
     * @param array $data
     * @param string|object $className
     * @return object
     */
    protected function response($data, $className = null)
    {
        $reflection = is_string($className) ? (new ReflectionClass($className)) :
                (is_null($className) ? (new ReflectionObject($this)) : (new ReflectionObject($className)));
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        if (empty($properties)) {
            $result = $data;
        } else {
            $result = [];
            foreach ($properties as $property) {
                $result[$property->getName()] = $data[$property->getName()] ?? null;
            }
        }
        return (object) $result;
    }

}
