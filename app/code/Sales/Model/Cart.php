<?php

namespace Seahinet\Sales\Model;

use Seahinet\Lib\Model\AbstractModel;

class Cart extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_cart', 'id', [
            'id', 'customer_id', 'status','additional', 'customer_note',
            'billing_address_id', 'shipping_address_id', 'warehouse_id',
            'store_id', 'billing_address', 'shipping_address', 'coupon',
            'is_virtual', 'free_shipping', 'base_currency', 'currency',
            'shipping_method', 'payment_method', 'base_shipping', 'shipping',
            'base_discount', 'discount', 'base_tax', 'tax', 'base_total', 'total'
        ]);
    }

}
