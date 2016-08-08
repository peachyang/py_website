<?php

namespace Seahinet\Sales\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Sales\Model\Collection\Invoice\Item as ItemCollection;

class Invoice extends AbstractModel
{

    protected $items = null;
    
    protected function construct()
    {
        $this->init('sales_order_invoice', 'id', [
            'id', 'order_id', 'increment_id', 'store_id', 'coupon',
            'base_currency', 'currency', 'base_shipping', 'shipping',
            'base_discount', 'discount', 'base_tax', 'tax',
            'base_total', 'total'
        ]);
    }

    public function getItems($force = false)
    {
        if ($force || is_null($this->items)) {
            $items = new ItemCollection();
            $items->where(['order_id' => $this->getId()]);
            $result = [];
            $items->walk(function($item) use (&$result) {
                $result[$item['id']] = $item;
            });
            $this->items = $result;
            if ($force) {
                return $items;
            }
        }
        return $this->items;
    }

}
