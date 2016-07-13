<?php

namespace Seahinet\Sales\Model\Cart;

use Seahinet\Lib\Model\AbstractModel;

class Item extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_order_item', 'id', [
            'id', 'order_id', 'product_id', 'product_name', 'options', 'qty',
            'sku', 'is_virtual', 'free_shipping', 'base_price', 'price',
            'base_discount', 'discount', 'base_tax', 'tax', 'base_total',
            'total', 'weight', 'status', 'warehouse_id'
        ]);
    }

}
