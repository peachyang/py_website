<?php

namespace Seahinet\Sales\Model;

use Seahinet\Customer\Model\Address;
use Seahinet\I18n\Model\Currency;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Lib\Model\Language;
use Seahinet\Lib\Model\Store;
use Seahinet\Sales\Model\Collection\CreditMemo;
use Seahinet\Sales\Model\Collection\Invoice;
use Seahinet\Sales\Model\Collection\Order\Item as ItemCollection;
use Seahinet\Sales\Model\Collection\Order\Status\History;
use Seahinet\Sales\Model\Collection\Shipment;
use Seahinet\Sales\Model\Order\Item;
use Seahinet\Sales\Model\Order\Status;

class Order extends AbstractModel
{

    protected $items = null;
    protected $phase = null;

    protected function construct()
    {
        $this->init('sales_order', 'id', [
            'id', 'status_id', 'increment_id', 'customer_id', 'language_id',
            'billing_address_id', 'shipping_address_id', 'warehouse_id', 'base_total_refund',
            'store_id', 'billing_address', 'shipping_address', 'coupon', 'total_refund',
            'is_virtual', 'free_shipping', 'base_currency', 'currency', 'base_subtotal',
            'shipping_method', 'payment_method', 'base_shipping', 'shipping', 'subtotal',
            'base_discount', 'discount', 'discount_detail', 'base_tax', 'tax', 'base_total', 'total',
            'base_total_paid', 'total_paid', 'additional', 'customer_note'
        ]);
    }

    public function place($warehouseId, $storeId, $statusId)
    {
        $cart = Cart::instance();
        $note = json_decode($cart->toArray()['customer_note'], true);
        $this->setData($cart->toArray())
                ->setData([
                    'shipping_method' => json_decode($cart->toArray()['shipping_method'], true)[$storeId],
                    'customer_note' => isset($note[$storeId]) ? $note[$storeId] : '',
                    'warehouse_id' => $warehouseId,
                    'store_id' => $storeId,
                    'language_id' => Bootstrap::getLanguage()->getId(),
                    'status_id' => $statusId
                ])->setId(null)->save();
        $orderId = $this->getId();
        $cart->getItems(true)->walk(function($item) use ($warehouseId, $storeId, $orderId) {
            if ($item['warehouse_id'] == $warehouseId && $item['store_id'] == $storeId) {
                if (is_array($item)) {
                    $item = new Item($item);
                } else {
                    $item = new Item($item->toArray());
                }
                $item->setData('order_id', $orderId)->setId(null)->save();
            }
        });
        $this->collateTotals();
        return $this;
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
            'base_shipping' => $this->getShippingMethod()->getShippingRate($items)
        ])->setData([
            'subtotal' => $currency->convert($this->storage['subtotal']),
            'shipping' => $currency->convert($this->storage['base_shipping'])
        ]);
        $this->getEventDispatcher()->trigger('tax.calc', ['model' => $this]);
        $this->getEventDispatcher()->trigger('promotion.calc', ['model' => $this]);
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

    public function getShippingAddress()
    {
        if (isset($this->storage['shipping_address_id'])) {
            $address = (new Address)->load($this->storage['shipping_address_id']);
            return $address->getId() ? $address : null;
        }
        return null;
    }

    public function getBillingAddress()
    {
        if (isset($this->storage['billing_address_id'])) {
            $address = (new Address)->load($this->storage['billing_address_id']);
            return $address->getId() ? $address : null;
        }
        return null;
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

    public function getCurrency()
    {
        if (isset($this->storage['currency'])) {
            return (new Currency)->load($this->storage['currency'], 'code');
        }
        return $this->getContainer()->get('currency');
    }

    public function getStatus()
    {
        if (isset($this->storage['status_id'])) {
            return (new Status)->load($this->storage['status_id']);
        }
        return null;
    }

    public function getPhase()
    {
        if (is_null($this->phase)) {
            $this->phase = $this->getStatus()->getPhase();
        }
        return $this->phase;
    }

    public function getStatusHistory()
    {
        if ($this->getId()) {
            $history = new History;
            $history->where(['order_id' => $this->getId()])
                    ->order('created_at DESC');
            return $history;
        }
        return [];
    }

    public function getInvoice()
    {
        if ($this->getId()) {
            $collection = new Invoice;
            $collection->where(['order_id' => $this->getId()]);
            return $collection;
        }
        return [];
    }

    public function getShipment()
    {
        if ($this->getId()) {
            $collection = new Shipment;
            $collection->where(['order_id' => $this->getId()]);
            return $collection;
        }
        return [];
    }

    public function getCreditMemo()
    {
        if ($this->getId()) {
            $collection = new CreditMemo;
            $collection->where(['order_id' => $this->getId()]);
            return $collection;
        }
        return [];
    }

    public function getQty()
    {
        $qty = 0;
        foreach ($this->getItems() as $item) {
            $qty += $item['qty'];
        }
        return $qty;
    }

    public function getLanguage()
    {
        if (isset($this->storage['language_id'])) {
            return (new Language)->load($this->storage['language_id']);
        }
        return null;
    }

    public function getStore()
    {
        if (isset($this->storage['store_id'])) {
            return (new Store)->load($this->storage['store_id']);
        }
        return null;
    }

    public function canCancel()
    {
        return in_array($this->getPhase()->offsetGet('code'), ['pending', 'pending_payment']);
    }

    public function canHold()
    {
        return $this->getPhase()->offsetGet('code') === 'processing';
    }

    public function canUnhold()
    {
        return $this->getPhase()->offsetGet('code') === 'holded';
    }

    public function canInvoice()
    {
        if (in_array($this->getPhase()->offsetGet('code'), ['complete', 'canceled', 'closed', 'holded'])) {
            return false;
        }
        $invoices = $this->getInvoice();
        $qty = $this->getQty();
        foreach ($invoices as $invoice) {
            foreach ($invoice->getItems() as $item) {
                $qty -= $item['qty'];
            }
        }
        return $qty > 0;
    }

    public function canShip()
    {
        if (in_array($this->getPhase()->offsetGet('code'), ['complete', 'canceled', 'closed', 'holded'])) {
            return false;
        }
        $shipments = $this->getShipment();
        $qty = $this->getQty();
        foreach ($shipments as $shipment) {
            foreach ($shipment->getItems() as $item) {
                $qty -= $item['qty'];
            }
        }
        return $qty > 0;
    }

    public function canRefund()
    {
        if ($this->getPhase()->offsetGet('code') !== 'holded') {
            return false;
        }
        $memos = $this->getCreditMemo();
        $qty = $this->getQty();
        foreach ($memos as $memo) {
            foreach ($memo->getItems() as $item) {
                $qty -= $item['qty'];
            }
        }
        return $qty > 0;
    }

}
