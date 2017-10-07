<?php

namespace Seahinet\Oauth\Model\Client;

use Seahinet\Oauth\Model\Client;

abstract class AbstractClient implements ClientInterface
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\Url;

    protected function request($url, $params = [], $method = 'GET')
    {
        $client = curl_init();
        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        curl_setopt($client, CURLOPT_URL, $url);
        if ($method === 'POST') {
            curl_setopt($client, CURLOPT_POST, 1);
            curl_setopt($client, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($client, CURLOPT_HEADER, 0);
        $response = curl_exec($client);
        curl_close($client);
        return $response;
    }

    public function valid($openId)
    {
        $client = new Client;
        $client->load([
            'oauth_server' => static::SERVER_NAME,
            'open_id' => $openId
        ]);
        return $client->getId() ?: 0;
    }

    public function available()
    {
        $config = $this->getContainer()->get('config');
        return $config['oauth/' . static::SERVER_NAME . '/enable'] &&
                $config['oauth/' . static::SERVER_NAME . '/appid'] &&
                $config['oauth/' . static::SERVER_NAME . '/secret'];
    }

}
