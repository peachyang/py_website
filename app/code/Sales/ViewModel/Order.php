<?php

namespace Seahinet\Sales\ViewModel;

use Seahinet\Customer\ViewModel\Account;
use Seahinet\Sales\Model\Collection\Order as Collection;

class Order extends Account
{

    public function getOrders()
    {
        $collection = new Collection;
        $collection->where(['sales_order.customer_id' => $this->getCustomer()->getId()])
                ->order('created_at DESC')->limit(10);
        $status = $this->getQuery('status');
        if ($status) {
            $collection->join('sales_order_status', 'sales_order_status.id=sales_order.status_id', [], 'left')
                    ->join('sales_order_phase', 'sales_order_status.phase_id=sales_order_phase.id', [], 'left');
            if ($status == 1) {
                $collection->where('(sales_order_phase.code=\'pending_payment\' OR sales_order_phase.code=\'pending\')');
            } else if ($status == 2) {
                $collection->where([
                    'sales_order_phase.code' => 'complete',
                    'sales_order_status.is_default' => 1
                ]);
            } else if ($status == 3) {
                $collection->join('review', 'review.order_id=sales_order.id', [], 'left')
                        ->where([
                            'sales_order_phase.code' => 'complete',
                            'sales_order_status.is_default' => 0
                        ])->having(['count(review.id)=0','sales_order.id is not null']);
            }
        }
        return $collection;
    }

    public function getLatestOrder()
    {
        $orders = $this->getOrders();
        return $orders->count() ? $orders[0] : false;
    }

}
