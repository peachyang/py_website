<?php

namespace Seahinet\Sales\Model\Cart;

use Seahinet\Catalog\Model\Product;
use Seahinet\Catalog\Model\Warehouse;
use Seahinet\Sales\Model\Cart;
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

    public function getCart()
    {
        if (isset($this->storage['cart_id'])) {
            return (new Cart)->load($this->storage['cart_id']);
        }
        return null;
    }

    public function &offsetGet($key)
    {
        $result = parent::offsetGet($key);
        if (!$result) {
            if ($key === 'product') {
                $result = new Product;
                $result->load($this->storage['product_id']);
            } else if ($key === 'store') {
                $result = new Store;
                $result->load($this->storage['store_id']);
            }
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
        $product = $this->offsetGet('product');
        $basePrice = $product->getFinalPrice($this->storage['qty'], false);
        $options = json_decode($this->storage['options'], true);
        $sum = 0;
        if (!empty($options)) {
            foreach ($product->getOptions() as $option) {
                if (isset($options[$option->getId()])) {
                    if (in_array($option->offsetGet('input'), ['select', 'radio', 'checkbox', 'multiselect'])) {
                        foreach ($option->getValues() as $value) {
                            if ($value['id'] == $options[$option->getId()]) {
                                $sum += $value['is_fixed'] ? $value['price'] : $basePrice * $value['price'] / 100;
                            }
                        }
                    } else {
                        $sum += $option['is_fixed'] ? $option['price'] : $basePrice * $option['price'] / 100;
                    }
                }
            }
        }
        $this->setData('base_price', $basePrice + $sum);
        $this->setData('price', $this->getCart()->getCurrency()->convert($this->storage['base_price']));
        $this->setData('base_total', $this->storage['base_price'] * $this->storage['qty'] + (isset($this->storage['base_tax']) ? $this->storage['base_tax'] : 0) + (isset($this->storage['base_discount']) ? $this->storage['base_discount'] : 0));
        $this->setData('total', $this->storage['price'] * $this->storage['qty'] + (isset($this->storage['tax']) ? $this->storage['tax'] : 0) + (isset($this->storage['discount']) ? $this->storage['discount'] : 0));
        return $this;
    }

}
