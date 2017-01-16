<?php

namespace Seahinet\LiveChat\ViewModel;

use Seahinet\Lib\ViewModel\Template;

class Chat extends Template
{

    public function __construct()
    {
        $this->setTemplate('livechat/chat');
    }

    public function getWsUrl()
    {
        $uri = $this->getRequest()->getUri();
        $config = $this->getConfig();
        return ($uri->getScheme() === 'https' ? 'wss:' : 'ws:') . $uri->withScheme('')
                        ->withFragment('')
                        ->withQuery('')
                        ->withPort($config['livechat/port'] ?: $uri->getPort())
                        ->withPath('/');
    }

}
