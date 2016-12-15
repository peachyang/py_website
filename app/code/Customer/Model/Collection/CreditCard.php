<?php

namespace Seahinet\Customer\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class CreditCard extends AbstractCollection
{

    protected function construct()
    {
        $this->init('customer_credit_card');
    }

}
