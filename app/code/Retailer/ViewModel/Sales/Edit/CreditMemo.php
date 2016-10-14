<?php

namespace Seahinet\Retailer\ViewModel\Sales\Edit;

use Seahinet\Retailer\ViewModel\Sales\View\Order;
use Seahinet\Sales\Model\Collection\CreditMemo\Item;
use Zend\Db\Sql\Expression;

class CreditMemo extends Order
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
