<?php

namespace Seahinet\Customer\ViewModel\Dashboard;

use Seahinet\Customer\ViewModel\Account;
use Seahinet\Sales\Model\Collection\Order as Collection;

class Order extends Account
{
    public function getPendingPayment()
    {
        $collection = new Order;
        $collection->columns(['id'])
                ->join('sales_order_status', 'sales_order_status.id=sales_order.status_id', [], 'left')
                ->join('sales_order_phase', 'sales_order_status.phase_id=sales_order_phase.id', [], 'left')
                ->where(['sales_order_phase.code' => 'pending_payment'])
                ->group('id');
        return count($collection);
    }

    public function getOrder()
    {
        $collection = new Collection;
        $collection->where([
            'customer_id' => $this->getCustomer()->getId()
        ])->order('created_at DESC')->limit(1);
        return $collection[0];
    }

}
