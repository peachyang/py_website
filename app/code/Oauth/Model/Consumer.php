<?php

namespace Seahinet\Oauth\Model;

use Seahinet\Lib\Model\AbstractModel;

class Consumer extends AbstractModel
{

    protected function construct()
    {
        $this->init('oauth_consumer', 'id', ['id', 'name', 'key', 'secret', 'callback_url', 'rejected_callback_url']);
    }

}
