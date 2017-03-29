<?php

namespace Seahinet\Api\Model\Api;

use SoapFault;
use Seahinet\Api\Model\Soap\User;
use Seahinet\Api\Model\Soap\Session;

final class General extends AbstractHandler
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\DataCache;

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
        $this->validateSessionId($sessionId, __FUNCTION__);
        $this->session->remove();
        return true;
    }

    /**
     * @param string $key
     * @param string $prefix
     */
    public function flushCache($key, $prefix)
    {
        $this->getContainer()->get('cache')->delete($key, $prefix, false);
    }

    /**
     * @param string $id
     * @param mixed $data
     * @param string $cacheKey
     * @param string $key
     */
    public function flushDataCacheRow($id, $data, $cacheKey, $key = null)
    {
        $this->flushRow($id, $data, $cacheKey, $key);
    }

    /**
     * @param string $cacheKey
     */
    public function flushDataCacheList($cacheKey)
    {
        $this->flushList($cacheKey);
    }

}
