<?php

namespace Seahinet\Oauth\Model;

use Seahinet\Lib\Model\AbstractModel;

class Token extends AbstractModel
{

    protected function construct()
    {
        $this->init('oauth_token', 'id', ['id', 'consumer_id', 'open_id', 'customer_id', 'status']);
    }

}
