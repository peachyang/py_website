<?php

namespace Seahinet\Customer\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Group extends AbstractCollection
{

    protected function construct()
    {
        $this->init('customer_group');
    }

}
