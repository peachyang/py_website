<?php

namespace Seahinet\Sales\Model\Shipment;

use Seahinet\Catalog\Model\Product;
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

    public function &offsetGet($key)
    {
        $result = parent::offsetGet($key);
        if (!$result) {
            if ($key === 'product') {
                $result = new Product;
                $result->load($this->storage['product_id']);
            }
        }
        return $result;
    }

}
