<?php

namespace Seahinet\Customer\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Balance extends AbstractCollection
{

    protected function construct()
    {
        $this->init('customer_balance');
    }

}
