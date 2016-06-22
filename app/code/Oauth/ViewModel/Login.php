<?php

namespace Seahinet\Oauth\ViewModel;

use Seahinet\Lib\ViewModel\AbstractViewModel;
use Seahinet\Oauth\Model\Consumer;

class Login extends AbstractViewModel
{

    protected $consumer = null;

    public function __construct()
    {
        $this->setTemplate('oauth/login');
    }

    public function setConsumer($consumer)
    {
        $this->consumer = $consumer;
        return $this;
    }

    public function getConsumer()
    {
        if (is_null($this->consumer)) {
            $this->consumer = new Consumer;
            $this->consumer->load($this->getQuery('client_id'), 'key');
        }
        return $this->consumer;
    }

}
