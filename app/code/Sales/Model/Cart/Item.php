<?php

namespace Seahinet\Sales\Model\Cart;

use Seahinet\Catalog\Model\Product;
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

    public function &offsetGet($key)
    {
        $result = parent::offsetGet($key);
        if (!$result && $key === 'product') {
            $result = new Product;
            $result->load($this->storage['product_id']);
        }
        return $result;
    }

    public function collateTotals()
    {
        $this->storage['base_total'] = $this->storage['base_price'] * $this->storage['qty'] + $this->storage['base_tax'] + $this->storage['base_discount'];
        $this->storage['total'] = $this->storage['price'] * $this->storage['qty'] + $this->storage['tax'] + $this->storage['discount'];
    }

}
