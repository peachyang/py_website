<?php

namespace Seahinet\Oauth\Model\Client;

interface ClientInterface
{

    /**
     * @return string
     */
    public function redirect();

    /**
     * @param string $token
     * @return array $result
     */
    public function access($token);

    /**
     * @param string $token
     * @param string $openId
     * @return array $result
     */
    public function getInfo($token, $openId);
}
