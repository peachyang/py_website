<?php

namespace Seahinet\Api\Model\Rest;

use Seahinet\Lib\Model\AbstractModel;

class Attribute extends AbstractModel
{

    protected function construct()
    {
        $this->init('api_rest_attribute', 'role_id', ['role_id', 'resource', 'operation', 'attributes']);
    }

    public function load($id, $key = null)
    {
        trigger_error('Call to undefined method Seahinet\\Api\\Model\\Attribute::load()', E_USER_ERROR);
    }

}
