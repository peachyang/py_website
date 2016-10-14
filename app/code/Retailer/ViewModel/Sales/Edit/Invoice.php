<?php

namespace Seahinet\Retailer\ViewModel\Sales\Edit;

use Seahinet\Retailer\ViewModel\Sales\View\Order;
use Seahinet\Sales\Model\Collection\Invoice\Item;
use Zend\Db\Sql\Expression;

class Invoice extends Order
{

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
