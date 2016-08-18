<?php

namespace Seahinet\Sales\Model\Invoice;

use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Sales\Model\Invoice;

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

    public function getInvoice()
    {
        if (isset($this->storage['invoice_id'])) {
            return (new Invoice)->load($this->storage['invoice_id']);
        }
        return null;
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
        $this->setData('price', $this->getInvoice()->getCurrency()->convert($this->storage['base_price']));
        $this->setData('base_total', $this->storage['base_price'] * $this->storage['qty'] + (isset($this->storage['base_tax']) ? $this->storage['base_tax'] : 0) + (isset($this->storage['base_discount']) ? $this->storage['base_discount'] : 0));
        $this->setData('total', $this->storage['price'] * $this->storage['qty'] + (isset($this->storage['tax']) ? $this->storage['tax'] : 0) + (isset($this->storage['discount']) ? $this->storage['discount'] : 0));
        return $this;
    }

}
