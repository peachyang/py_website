<?php

namespace Seahinet\Customer\ViewModel\Dashboard;

use Seahinet\Customer\ViewModel\Account;
use Seahinet\Sales\Model\Collection\Order;

class Profile extends Account
{

    public function getPendingPayment()
    {
        $collection = new Order;
        $collection->columns(['id'])
                ->join('sales_order_status', 'sales_order_status.id=sales_order.status_id', [], 'left')
                ->join('sales_order_phase', 'sales_order_status.phase_id=sales_order_phase.id', [], 'left')
                ->where([
                    'sales_order_phase.code' => 'pending_payment',
                    'customer_id' => $this->getCustomer()->getId()
                ])
                ->group('id');
        return count($collection);
    }

    public function getShipping()
    {
        $collection = new Order;
        $collection->columns(['id'])
                ->join('sales_order_status', 'sales_order_status.id=sales_order.status_id', [], 'left')
                ->join('sales_order_phase', 'sales_order_status.phase_id=sales_order_phase.id', [], 'left')
                ->where([
                    'sales_order_phase.code' => 'complete',
                    'sales_order_status.is_default' => 1,
                    'customer_id' => $this->getCustomer()->getId()
                ])->group('id');
        return count($collection);
    }

    public function getReviewing()
    {
        $collection = new Order;
        $collection->columns(['id'])
                ->join('sales_order_status', 'sales_order_status.id=sales_order.status_id', [], 'left')
                ->join('sales_order_phase', 'sales_order_status.phase_id=sales_order_phase.id', [], 'left')
                ->where([
                    'sales_order_phase.code' => 'complete',
                    'sales_order_status.is_default' => 0,
                    'customer_id' => $this->getCustomer()->getId()
                ])->group('id');
        return count($collection);
    }

}
