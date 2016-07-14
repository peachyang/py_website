<?php

namespace Seahinet\Sales\Model\Shipment;

use Seahinet\Lib\Model\AbstractModel;

class Item extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_order_shipment_item', 'id', [
            'id', 'item_id', 'shipment_id', 'product_id', 'product_name',
            'options', 'qty', 'sku', 'weight'
        ]);
    }

}
