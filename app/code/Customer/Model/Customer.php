<?php

namespace Seahinet\Customer\Model;

use Seahinet\Lib\Model\Eav\Entity;

class Customer extends Entity
{

    protected function construct()
    {
        $this->init('customer');
    }

}
