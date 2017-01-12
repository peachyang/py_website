<?php

namespace Seahinet\Oauth\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Api\Model\Rest\Role;

class Consumer extends AbstractModel
{

    protected function construct()
    {
        $this->init('oauth_consumer', 'id', ['id', 'name', 'role_id', 'key', 'secret', 'callback_url', 'rejected_callback_url']);
    }

    public function getRole()
    {
        if (!empty($this->storage['role_id'])) {
            $role = new Role;
            $role->load($this->storage['role_id']);
            return $role;
        }
        return null;
    }

}
