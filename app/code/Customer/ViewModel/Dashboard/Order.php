<?php

namespace Seahinet\Customer\ViewModel\Dashboard;

use Seahinet\Customer\ViewModel\Account;
use Seahinet\Sales\Model\Collection\Order as Collection;

class Order extends Account
{

    public function getOrder()
    {
        $collection = new Collection;
        $collection->where([
            'customer_id' => $this->getCustomer()->getId()
        ])->order('created_at DESC')->limit(1);
        return $collection[0];
    }

}
