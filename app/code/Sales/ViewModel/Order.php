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
                ->order('created_at DESC')->limit(10);
        return $collection;
    }

}
