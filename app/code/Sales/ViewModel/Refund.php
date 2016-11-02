<?php

namespace Seahinet\Sales\ViewModel;

use Seahinet\Customer\ViewModel\Account;
use Seahinet\Sales\Model\Collection\Order as Collection;
use Seahinet\Sales\Source\RefundReason;

class Refund extends Account
{

    public function getOrders()
    {
        $collection = new Collection;
        $collection->where(['customer_id' => $this->getCustomer()->getId()])
                ->join('sales_order_status', 'sales_order_status.id=sales_order.status_id', [], 'left')
                ->join('sales_order_phase', 'sales_order_status.phase_id=sales_order_phase.id', [], 'left')
        ->where->in('sales_order_phase.code', ['processing', 'complete', 'holded']);
        $result = [];
        $collection->walk(function($item) use (&$result) {
            if ($item->canRefund(false)) {
                $result[] = $item;
            }
        });
        return $result;
    }

    public function getDefaultOrder()
    {
        return $this->getQuery('id', false);
    }

    public function getReasons()
    {
        return (new RefundReason)->getSourceArray();
    }

}
