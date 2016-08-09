<?php

namespace Seahinet\Sales\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\I18n\Model\Currency;
use Seahinet\Sales\Model\Collection\CreditMemo\Item as ItemCollection;

class CreditMemo extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_order_creditmemo', 'id', [
            'id', 'order_id', 'increment_id', 'store_id', 'warehouse_id',
            'base_currency', 'currency', 'base_shipping', 'shipping',
            'base_discount', 'discount', 'base_tax', 'tax',
            'base_total', 'total', 'comment', 'status'
        ]);
    }

    public function getCurrency()
    {
        if (isset($this->storage['currency'])) {
            $currency = new Currency;
            $currency->labol = 'invoice';
            return $currency->load($this->storage['currency'], 'code');
        }
        return $this->getContainer()->get('currency');
    }
    
    public function getShippingMethod()
    {
        if (isset($this->storage['shipping_method'])) {
            $className = $this->getContainer()->get('config')['shipping/' . $this->storage['shipping_method'] . '/model'];
            return new $className;
        }
        return null;
    }
    
    public function getPaymentMethod()
    {
        if (isset($this->storage['payment_method'])) {
            $className = $this->getContainer()->get('config')['payment/' . $this->storage['payment_method'] . '/model'];
            return new $className;
        }
        return null;
    }

    public function getItems($force = false)
    {
        if ($force || is_null($this->items)) {
            $items = new ItemCollection();
            $items->where(['order_id' => $this->getId()]);
            $result = [];
            $items->walk(function($item) use (&$result) {
                $result[$item['id']] = $item;
            });
                $this->items = $result;
                if ($force) {
                    return $items;
                }
        }
        return $this->items;
    }
    
}
