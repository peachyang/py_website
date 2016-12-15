<?php

namespace Seahinet\Oauth\Model;

use Seahinet\Lib\Model\AbstractModel;

class Client extends AbstractModel
{

    protected function construct()
    {
        $this->init('oauth_client', 'customer_id', ['customer_id', 'oauth_server', 'open_id']);
    }

    public function load($id, $key = null)
    {
        trigger_error('Call to undefined method Seahinet\\Oauth\\Model\\Client::load()', E_USER_ERROR);
    }

    public function save($constraint = [], $insertForce = true)
    {
        return parent::save($constraint, $insertForce);
    }

}
