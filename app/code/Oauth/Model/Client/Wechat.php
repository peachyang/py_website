<?php

namespace Seahinet\Oauth\Model\Client;

use Seahinet\Lib\Session\Segment;
use Zend\Math\Rand;

class Wechat extends AbstractClient
{

    const SERVER_NAME = 'wechat';

    public function redirect()
    {
        $config = $this->getContainer()->get('config');
        $segment = new Segment('oauth');
        $state = Rand::getString(8);
        $segment->set('server', static::SERVER_NAME)
                ->set('state', $state);
        return (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessager') === false ?
                'https://open.weixin.qq.com/connect/qrconnect?appid=' :
                'https://open.weixin.qq.com/connect/oauth2/authorize?appid=') . $config['oauth/wechat/appid'] .
                '&redirect_uri=' . rawurlencode($this->getBaseUrl('oauth/response/')) .
                '&response_type=code&scope=snsapi_userinfo&state=' . $state .
                '#wechat_redirect';
    }

    public function access($token)
    {
        $config = $this->getContainer()->get('config');
        $result = json_decode($this->request('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $config['oauth/wechat/appid'] .
                        '&secret=' . $config['oauth/wechat/secret'] . '&code=' . $token . '&grant_type=authorization_code'), true);
        return [$result['access_token'], $result['openid']];
    }

    public function getInfo($token, $openId)
    {
        return json_decode($this->request('https://api.weixin.qq.com/sns/userinfo?access_token=' . $token . '&openid=' . $openId), true);
    }

}
