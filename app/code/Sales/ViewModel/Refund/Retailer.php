<?php

namespace Seahinet\Sales\ViewModel\Refund;

use Seahinet\Retailer\ViewModel\AbstractViewModel;
use Seahinet\Sales\Model\Collection\Rma;

class Retailer extends AbstractViewModel
{

    public function getApplication()
    {
        $collection = new Rma;
        $collection->join('sales_order', 'sales_order.id=sales_rma.order_id', [], 'left')
                ->join('retailer', 'sales_order.store_id=retailer.store_id', [], 'left')
                ->where(['retailer.id' => $this->getRetailer()->getId()]);
        return $collection;
    }

    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

}
