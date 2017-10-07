<?php

namespace Seahinet\Retailer\ViewModel\Sales\Edit;

use Seahinet\Admin\ViewModel\Sales\View\Order;
use Seahinet\Sales\Model\Collection\Shipment\Item;
use Seahinet\Shipping\Source\Carrier;
use Zend\Db\Sql\Expression;

class Shipment extends Order
{

    public function getCarriers()
    {
        return (new Carrier)->getSourceArray();
    }

    public function getMaxQty($item)
    {
        $items = new Item;
        $items->columns(['sum' => new Expression('sum(qty)')])
                ->group('item_id')
                ->having(['item_id' => $item->getId()]);
        if ($items->count()) {
            return $item['qty'] - $items[0]['sum'];
        }
        return $item['qty'];
    }

}
