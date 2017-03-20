<?php

namespace Seahinet\Oauth\Model\Api\Soap;

use Seahinet\Api\Model\Api\AbstractHandler;
use Seahinet\Oauth\Model\Client;

class Oauth extends AbstractHandler
{

    /**
     * @param string $sessionId
     * @param string $serverName
     * @param string $openId
     * @return int
     */
    public function oauthLogin($sessionId, $serverName, $openId)
    {
        $this->validateSessionId($sessionId, __FUNCTION__);
        $client = new Client;
        $client->load([
            'oauth_server' => $serverName,
            'open_id' => $openId
        ]);
        return $client->getId() ?: 0;
    }

    /**
     * @param string $sessionId
     * @param int $customerId
     * @param string $serverName
     * @param string $openId
     */
    public function oauthBind($sessionId, $customerId, $serverName, $openId)
    {
        $this->validateSessionId($sessionId, __FUNCTION__);
        $client = new Client;
        $client->setData([
            'customer_id' => $customerId,
            'oauth_server' => $serverName,
            'open_id' => $openId
        ])->save();
    }

}
