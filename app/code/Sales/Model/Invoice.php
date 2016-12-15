<?php

namespace Seahinet\Sales\Model;

use Seahinet\I18n\Model\Currency;
use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Sales\Model\Collection\Invoice\Item as ItemCollection;

class Invoice extends AbstractModel
{

    protected $items = null;

    protected function construct()
    {
        $this->init('sales_order_invoice', 'id', [
            'id', 'order_id', 'increment_id', 'store_id', 'coupon', 'base_subtotal',
            'base_currency', 'currency', 'base_shipping', 'shipping', 'subtotal',
            'base_discount', 'discount', 'base_tax', 'tax',
            'base_total', 'total'
        ]);
    }

    public function &offsetGet($key)
    {
        $result = parent::offsetGet($key);
        if (substr($key, 0, 5) === 'base_' && is_numeric($result) && $this->getContainer()->get('currency')['code'] !== $this->storage['base_currency']) {
            $result = $this->getBaseCurrency()->rconvert($result);
        }
        return $result;
    }

    public function getItems($force = false)
    {
        if ($force || is_null($this->items)) {
            $items = new ItemCollection();
            $items->where(['invoice_id' => $this->getId()]);
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

    public function getBaseCurrency()
    {
        if (isset($this->storage['base_currency'])) {
            return (new Currency)->load($this->storage['base_currency'], 'code');
        }
        return $this->getContainer()->get('currency');
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

    public function getOrder()
    {
        return isset($this->storage['order_id']) ?
                (new Order)->load($this->storage['order_id']) : null;
    }

    public function collateTotals()
    {
        $baseCurrency = $this->getContainer()->get('config')['i18n/currency/base'];
        $currency = (new Currency)->load($this->getContainer()->get('request')->getCookie('currency', $baseCurrency));

        $items = $this->getItems(true);
        $baseSubtotal = 0;
        foreach ($items as $item) {
            $baseSubtotal += $item->offsetGet('base_price') * $item->offsetGet('qty');
        }
        $this->setData([
            'base_subtotal' => $baseSubtotal,
        ])->setData([
            'subtotal' => $currency->convert($this->storage['base_subtotal'])
        ]);
        $this->setData([
            'base_total' => $this->storage['base_subtotal'] +
            $this->storage['base_shipping'] +
            $this->storage['base_tax'] +
            $this->storage['base_discount'],
            'total' => $this->storage['subtotal'] +
            $this->storage['shipping'] +
            $this->storage['tax'] +
            $this->storage['discount']
        ]);
        $this->save();
        return $this;
    }

}
