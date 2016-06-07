<?php

namespace Seahinet\Customer\Model;

use Seahinet\Lib\Model\AbstractModel;

class Group extends AbstractModel
{

    protected function construct()
    {
        $this->init('customer_group', 'id', ['id', 'name']);
    }

}
