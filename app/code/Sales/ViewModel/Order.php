<?php

namespace Seahinet\Sales\ViewModel;

use Seahinet\Customer\ViewModel\Account;
use Seahinet\Sales\Model\Collection\Order as Collection;

class Order extends Account
{

    public function getOrders()
    {
        $collection = new Collection;
        $collection->where(['customer_id' => $this->getCustomer()->getId()])
                ->limit(20);
        return $collection;
    }

}
