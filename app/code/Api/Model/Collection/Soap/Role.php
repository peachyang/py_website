<?php

namespace Seahinet\Api\Model\Collection\Soap;

use Seahinet\Lib\Model\AbstractCollection;

class Role extends AbstractCollection
{

    protected function construct()
    {
        $this->init('api_soap_role');
    }

}
