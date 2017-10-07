<?php

namespace Seahinet\Oauth\Model;

use Seahinet\Lib\Model\AbstractModel;

class Client extends AbstractModel
{

    protected function construct()
    {
        $this->init('oauth_client', 'customer_id', ['customer_id', 'oauth_server', 'open_id']);
    }

    public function save($constraint = [], $insertForce = true)
    {
        return parent::save($constraint, $insertForce);
    }

}
