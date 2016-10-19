<?php

namespace Seahinet\Sales\Model\CreditMemo;

use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Sales\Model\CreditMemo;

class Item extends AbstractModel
{

    protected $product = null;

    protected function construct()
    {
        $this->init('sales_order_creditmemo_item', 'id', [
            'id', 'item_id', 'creditmemo_id', 'product_id', 'product_name',
            'options', 'qty', 'sku', 'base_price', 'price', 'base_discount',
            'discount', 'base_tax', 'tax', 'base_total', 'total'
        ]);
    }

    public function &offsetGet($key)
    {
        $result = parent::offsetGet($key);
        if (!$result) {
            if ($key === 'product') {
                if (is_null($this->product)) {
                    $this->product = new Product;
                    $this->product->load($this->storage['product_id']);
                }
                $result = $this->product;
            }
        }
        return $result;
    }

    public function getCreditMemo()
    {
        if (isset($this->storage['creditmemo_id'])) {
            return (new CreditMemo)->load($this->storage['creditmemo_id']);
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
        $this->setData('price', $this->getCreditMemo()->getCurrency()->convert($this->storage['base_price']));
        $this->setData('base_total', $this->storage['base_price'] * $this->storage['qty'] + ($this->storage['base_tax'] ?? 0) + ($this->storage['base_discount'] ?? 0));
        $this->setData('total', $this->storage['price'] * $this->storage['qty'] + ($this->storage['tax'] ?? 0) + ($this->storage['discount'] ?? 0));
        return $this;
    }

}
