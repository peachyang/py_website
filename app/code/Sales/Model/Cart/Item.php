<?php

namespace Seahinet\Sales\Model\Cart;

use Seahinet\Catalog\Model\Product;
use Seahinet\Catalog\Model\Warehouse;
use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Lib\Model\Store;

class Item extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_cart_item', 'id', [
            'id', 'cart_id', 'product_id', 'product_name', 'options', 'qty',
            'sku', 'is_virtual', 'free_shipping', 'base_price', 'price',
            'base_discount', 'discount', 'base_tax', 'tax', 'base_total',
            'total', 'weight', 'status', 'warehouse_id', 'store_id'
        ]);
    }

    public function &offsetGet($key)
    {
        $result = parent::offsetGet($key);
        if (!$result && $key === 'product') {
            $result = new Product;
            $result->load($this->storage['product_id']);
        } else if ($key === 'store') {
            $result = new Store;
            $result->load($this->storage['store_id']);
        }
        return $result;
    }

    public function getInventory()
    {
        $warehouse = new Warehouse;
        return $warehouse->setId($this->storage['warehouse_id'])->getInventory($this->storage['product_id'], $this->storage['sku']);
    }

    public function collateTotals()
    {
        $this->storage['base_total'] = $this->storage['base_price'] * $this->storage['qty'] + (isset($this->storage['base_tax']) ? $this->storage['base_tax'] : 0) + (isset($this->storage['base_discount']) ? $this->storage['base_discount'] : 0);
        $this->storage['total'] = $this->storage['price'] * $this->storage['qty'] + (isset($this->storage['tax']) ? $this->storage['tax'] : 0) + (isset($this->storage['discount']) ? $this->storage['discount'] : 0);
        return $this;
    }

    public function getOptions()
    {
        if ($this->storage['product_id']) {
            $options = json_decode($this->storage['options']);
            $result = [];
            foreach($options as $id => $value){
                $option = new Product\Option;
                $option->load($id);
                $result[$option->getLabel()] = $option->getValue($value);
            }
        }
    }

}
