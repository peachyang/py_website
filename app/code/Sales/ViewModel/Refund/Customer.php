<?php

namespace Seahinet\Sales\ViewModel\Refund;

use Seahinet\Customer\ViewModel\Account;
use Seahinet\Sales\Model\Collection\Rma;

class Customer extends Account
{

    public function getApplication()
    {
        $collection = new Rma;
        $collection->where(['customer_id' => $this->getCustomer()->getId()]);
        return $collection;
    }

}
