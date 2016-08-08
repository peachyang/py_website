<?php

namespace Seahinet\Sales\Model;

use Seahinet\Catalog\Model\Product;
use Seahinet\Customer\Model\Address;
use Seahinet\I18n\Model\Currency;
use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Sales\Model\Collection\Invoice;
use Seahinet\Sales\Model\Collection\Order\Item as ItemCollection;
use Seahinet\Sales\Model\Collection\Shipment;
use Seahinet\Sales\Model\Order\Item;
use Seahinet\Sales\Model\Order\Status;

class Order extends AbstractModel
{

    protected $items = null;

    protected function construct()
    {
        $this->init('sales_order', 'id', [
            'id', 'status_id', 'increment_id', 'customer_id',
            'billing_address_id', 'shipping_address_id', 'warehouse_id',
            'store_id', 'billing_address', 'shipping_address', 'coupon',
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

    public function getQty()
    {
        $qty = 0;
        foreach ($this->getItems() as $item) {
            $qty += $item['qty'];
        }
        return $qty;
    }

}
