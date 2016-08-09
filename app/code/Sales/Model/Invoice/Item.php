<?php

namespace Seahinet\Sales\Model\Invoice;

use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Model\AbstractModel;

class Item extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_order_invoice_item', 'id', [
            'id', 'item_id', 'invoice_id', 'product_id', 'product_name',
            'options', 'qty', 'sku', 'base_price', 'price', 'base_discount',
            'discount', 'base_tax', 'tax', 'base_total', 'total'
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

    public function collateTotals()
    {
        $this->storage['base_total'] = $this->storage['base_price'] * $this->storage['qty'] + (isset($this->storage['base_tax']) ? $this->storage['base_tax'] : 0) + (isset($this->storage['base_discount']) ? $this->storage['base_discount'] : 0);
        $this->storage['total'] = $this->storage['price'] * $this->storage['qty'] + (isset($this->storage['tax']) ? $this->storage['tax'] : 0) + (isset($this->storage['discount']) ? $this->storage['discount'] : 0);
        return $this;
    }

}
