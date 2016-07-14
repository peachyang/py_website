<?php

namespace Seahinet\Api\Model\Soap;

use Seahinet\Lib\Model\AbstractModel;

/**
 * Description of SoapRole
 *
 * @author lenovo
 */
class Role extends AbstractModel
{

    protected $role = null;

    protected function construct()
    {
        $this->init('api_soap_role', 'id', ['id', 'name']);
    }

}
