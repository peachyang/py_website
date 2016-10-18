<?php

namespace Seahinet\Retailer\ViewModel;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Lib\Session\Segment;
use Seahinet\Retailer\Model\Retailer;
use Seahinet\Retailer\Model\Collection\After as Collection;
use Seahinet\Sales\Model\Collection\Order;

class After extends Template
{

    public function getAfterCollection()
    {
        $segment = new Segment('customer');
        $retailer = new Retailer;
        $retailer->load($segment->get('customer')->getId(), 'customer_id');
        $collection = new Order;
        $collection->join('rma', 'rma.order_id=sales_order.id', ['refund_status' => 'status'], 'left')
                    ->where([
                    'store_id' => $retailer->offsetGet('store_id'),
                ])->order('created_at DESC')->where->isNotNull('rma.order_id');
        return $collection;
    }

}
