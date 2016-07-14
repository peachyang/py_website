<?php

namespace Seahinet\Sales\Model;

use Seahinet\Lib\Model\AbstractModel;

class Shipment extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_order_shipment', 'id', [
            'id', 'order_id', 'increment_id', 'customer_id', 'store_id',
            'shipping_method', 'billing_address_id', 'shipping_address_id',
            'warehouse_id', 'store_id', 'billing_address', 'shipping_address',
            'comment', 'status'
        ]);
    }

}
