<?php

namespace Seahinet\Customer\Model\Collection;

use Seahinet\Lib\Model\Collection\Eav\Collection;

class Address extends Collection
{

    const ENTITY_TYPE = 'address';

    protected function construct()
    {
        $this->init('address');
    }

}
