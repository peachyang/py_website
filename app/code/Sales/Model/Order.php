<?php

namespace Seahinet\Sales\Model;

use Seahinet\Lib\Model\AbstractModel;

class Order extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_order', 'id', [
            'id', 'status_id', 'increment_id', 'customer_id',
            'billing_address_id', 'shipping_address_id', 'warehouse_id',
            'store_id', 'billing_address', 'shipping_address', 'coupon',
            'is_virtual', 'free_shipping', 'base_currency', 'currency',
            'shipping_method', 'payment_method', 'base_shipping', 'shipping',
            'base_discount', 'discount', 'base_tax', 'tax', 'base_total', 'total',
            'base_total_paid', 'total_paid', 'additional', 'customer_note'
        ]);
    }

}
