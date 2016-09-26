<?php

namespace Seahinet\Sales\Model;

use Exception;
use Seahinet\Catalog\Model\Product;
use Seahinet\Customer\Model\Address;
use Seahinet\I18n\Model\Currency;
use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Stdlib\Singleton;
use Seahinet\Sales\Model\Cart\Item;
use Seahinet\Sales\Model\Collection\Cart as Collection;
use Seahinet\Sales\Model\Collection\Cart\Item as ItemCollection;

final class Cart extends AbstractModel implements Singleton
{

    protected static $instance = null;
    protected $items = null;

    protected function construct()
    {
        $this->init('sales_cart', 'id', [
            'id', 'customer_id', 'status', 'additional', 'customer_note', 'discount_detail',
            'billing_address_id', 'shipping_address_id', 'billing_address', 'shipping_address',
            'is_virtual', 'free_shipping', 'base_currency', 'currency', 'base_subtotal',
            'shipping_method', 'payment_method', 'base_shipping', 'shipping', 'subtotal',
            'base_discount', 'discount', 'base_tax', 'tax', 'base_total', 'total', 'coupon'
        ]);
    }

    public function initInstance()
    {
        $baseCurrency = $this->getContainer()->get('config')['i18n/currency/base'];
        $currency = $this->getContainer()->get('request')->getCookie('currency', $baseCurrency);
        $segment = new Segment('customer');
        if ($segment->get('cart')) {
            $this->load($segment->get('cart'));
        } else if ($segment->get('hasLoggedIn')) {
            $collection = new Collection;
            $collection->where([
                'customer_id' => $segment->get('customer')->getId(),
                'status' => 1
            ])->order('id DESC');
            if ($collection->count()) {
                $this->setData($collection->toArray()[0]);
            }
        }
        if (!$this->getId()) {
            $this->regenerate($this, $currency, $baseCurrency);
        } else {
            if ($this->storage['currency'] !== $currency) {
                $this->convertPrice($currency);
            }
            if ($this->storage['base_currency'] !== $baseCurrency) {
                $this->convertBasePrice($baseCurrency);
            }
        }
    }

    /**
     * @return Cart
     */
    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
            static::$instance->initInstance();
        }
        return static::$instance;
    }

    public function abandon()
    {
        if ($this->storage['status']) {
            $this->setData('status', 0)->save();
        }
        $segment = new Segment('customer');
        $segment->offsetUnset('cart');
        static::$instance = null;
    }

    public function combine($cart)
    {
        $id = $this->getId();
        try {
            $this->beginTransaction();
            foreach ($cart->getItems() as $item) {
                $this->addItem($item['product_id'], $item['qty'], $item['warehouse_id'], json_decode($item['options'], true), $item['sku'], false);
            }
            $cart->setData('status', 0)->save();
            $this->collateTotals();
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            $this->getContainer()->get('log')->logException($e);
        }
        $segment = new Segment('customer');
        $segment->set('cart', $id);
        static::$instance = $this;
    }

    private function regenerate($cart = null, $currency = null, $baseCurrency = null)
    {
        $segment = new Segment('customer');
        if (is_null($baseCurrency)) {
            $baseCurrency = $this->getContainer()->get('config')['i18n/currency/base'];
        }
        if (is_null($currency)) {
            $currency = $this->getContainer()->get('request')->getCookie('currency', $baseCurrency);
        }
        if (is_null($cart)) {
            $cart = new static;
        }
        $cart->setData([
            'base_currency' => $baseCurrency,
            'currency' => $currency
        ]);
        if ($segment->get('hasLoggedIn')) {
            $cart->setData([
                'customer_id' => $segment->get('customer')->getId()
            ]);
        }
        $cart->save();
        $segment->set('cart', $cart->getId());
        return $cart;
    }

    public function getItem($id)
    {
        if(is_null($this->items)){
            $this->getItems();
        }
        if (isset($this->items[$id])) {
            return $this->items[$id];
        } else {
            $items = new ItemCollection();
            $items->where(['cart_id' => $this->getId(), 'id' => $id])->order('store_id, warehouse_id');
            if ($items->count()) {
                return $items[0];
            }
        }
        return null;
    }

    public function getItems($force = false)
    {
        if ($force || is_null($this->items)) {
            $items = new ItemCollection();
            $items->where(['cart_id' => $this->getId()]);
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

    public function isVirtual()
    {
        foreach ($this->getItems() as $item) {
            if (!$item['is_virtual']) {
                return false;
            }
        }
        return true;
    }

    public function addItem($productId, $qty, $warehouseId, array $options = [], $sku = '', $collate = true)
    {
        $product = new Product;
        $product->load($productId);
        if (!$sku) {
            $sku = $product['sku'];
        }
        ksort($options);
        $this->getEventDispatcher()->trigger('cart.add.before', [
            'product_id' => $productId,
            'qty' => $qty,
            'warehouse_id' => $warehouseId,
            'sku' => $sku,
            'options' => $options
        ]);
        $items = new ItemCollection();
        $items->where([
            'cart_id' => $this->getId(),
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'store_id' => $product['store_id'],
            'sku' => $sku,
            'options' => json_encode($options)
        ]);
        $item = new Item;
        if ($items->count()) {
            $newQty = $items[0]['qty'] + $qty;
            $item->setData($items[0]->toArray())
                    ->setData([
                        'qty' => $newQty,
                        'base_price' => $product->getFinalPrice($newQty, false),
                        'price' => $product->getFinalPrice($newQty)
                    ])->collateTotals()->save();
        } else {
            $item->setData([
                'cart_id' => $this->getId(),
                'product_id' => $productId,
                'product_name' => $product['name'],
                'store_id' => $product['store_id'],
                'qty' => $qty,
                'is_virtual' => $product->isVirtual() ? 1 : 0,
                'options' => json_encode($options),
                'sku' => $sku,
                'warehouse_id' => $warehouseId,
                'weight' => $product['weight'] * $qty,
                'base_price' => $product->getFinalPrice($qty, false),
                'price' => $product->getFinalPrice($qty)
            ])->collateTotals()->save();
        }
        $this->items[$item->getId()] = $item->toArray();
        if ($collate) {
            $this->collateTotals();
        }
        $this->getEventDispatcher()->trigger('cart.add.after', [
            'product_id' => $productId,
            'qty' => $qty,
            'warehouse_id' => $warehouseId,
            'sku' => $sku,
            'options' => $options
        ]);
        return $this;
    }

    public function changeQty($item, $qty, $collate = true)
    {
        if (is_numeric($item)) {
            $item = (new Item)->load($item);
        }
        $inventory = $item['product']->getInventory($item['warehouse_id'], $item['sku']);
        if ($item['qty'] > $qty && $inventory['min_qty'] <= $qty || $item['qty'] < $qty && $inventory['max_qty'] >= $qty) {
            $this->getEventDispatcher()->trigger('cart.add.before', [
                'product_id' => $item['product_id'],
                'qty' => $qty,
                'warehouse_id' => $item['warehouse_id'],
                'sku' => $item['sku'],
                'options' => $item['options']
            ]);
            $item->setData(['qty' => $qty, 'status' => 1])->collateTotals()->save();
            $this->items[$item->getId()] = $item->toArray();
        } else if ($item['qty'] == $qty && $item['status'] == 0) {
            return $this->changeItemStatus($item, true, $collate);
        }
        if ($collate) {
            $this->collateTotals();
        }
        return $this;
    }

    public function changeItemStatus($item, $status, $collate = true)
    {
        if (is_numeric($item)) {
            $item = (new Item)->load($item);
        }
        $item->setData(['status' => $status])->collateTotals()->save();
        $this->items[$item->getId()]['status'] = $status;
        if ($collate) {
            $this->collateTotals();
        }
        return $this;
    }

    public function removeItem($item)
    {
        if (is_numeric($item)) {
            unset($this->items[$item]);
            $item = (new Item)->setData('id', $item);
        } else {
            unset($this->items[$item['id']]);
        }
        $item->remove();
        $this->collateTotals();
        return $this;
    }

    public function removeItems($items)
    {
        if (is_array($items) || $items instanceof \Traversable) {
            foreach ($items as $item) {
                if (is_numeric($item)) {
                    unset($this->items[$item]);
                    $item = (new Item)->load($item);
                } else {
                    unset($this->items[$item['id']]);
                }
                $item->remove();
            }
            $this->collateTotals();
        }
        return $this;
    }

    public function removeAllItems()
    {
        foreach ($this->getItems() as $item) {
            $item = new Item;
            $item->setId($item['id'])->remove();
        }
        $this->items = [];
        $this->collateTotals();
        return $this;
    }

    public function convertPrice($currency)
    {
        try {
            $this->beginTransaction();
            if (is_string($currency)) {
                $currency = (new Currency)->load($currency, 'code');
            }
            foreach ($this->getItems() as $item) {
                $item = new Item($item);
                foreach (['price', 'tax', 'discount', 'total'] as $attr) {
                    $item->setData($attr, $currency->convert($item->offsetGet('base_' . $attr)));
                }
                $item->save();
            }
            foreach (['shipping', 'tax', 'discount', 'total'] as $attr) {
                $this->setData($attr, $currency->convert($this->storage['base_' . $attr]));
            }
            $this->setData('currency', $currency->offsetGet('code'))->save();
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            $this->getContainer()->get('log')->logException($e);
        }
    }

    public function convertBasePrice($currency)
    {
        try {
            $this->beginTransaction();
            if (is_string($currency)) {
                $currency = new Currency;
                $currency->load($currency, 'code');
            }
            foreach (['base_shipping', 'base_tax', 'base_discount', 'base_total'] as $attr) {
                $this->offsetSet($attr, $currency->convert($this->storage[$attr]));
            }
            foreach ($this->getItems() as $item) {
                $item = new Item($item);
                foreach (['base_price', 'base_tax', 'base_discount', 'base_total'] as $attr) {
                    $item->offsetSet($attr, $currency->convert($item->offsetGet($attr)));
                }
                $item->save();
            };
            $this->offsetSet('base_currency', $currency->offsetGet('code'));
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            $this->getContainer()->get('log')->logException($e);
        }
    }

    public function collateTotals()
    {
        $baseCurrency = $this->getContainer()->get('config')['i18n/currency/base'];
        $currency = (new Currency)->load($this->getContainer()->get('request')->getCookie('currency', $baseCurrency));

        $items = $this->getItems(true);
        $baseSubtotal = 0;
        $storeId = [];
        foreach ($items as $item) {
            $baseSubtotal += $item->offsetGet('base_price') * $item->offsetGet('qty');
            if (!isset($storeId[$item['store_id']])) {
                $storeId[$item['store_id']] = [];
            }
            $storeId[$item['store_id']][] = $item;
        }
        $shipping = 0;
        if (!$this->offsetGet('free_shipping') && !$this->offsetGet('is_virtual')) {
            foreach ($storeId as $id => $i) {
                if ($method = $this->getShippingMethod($id)) {
                    $shipping += $method->getShippingRate($i);
                }
            }
        }
        $this->setData([
            'base_subtotal' => $baseSubtotal,
            'base_shipping' => $shipping,
            'base_discount' => 0,
            'discount' => 0,
            'discount_detail' => '',
            'base_tax' => 0,
            'tax' => 0
        ])->setData([
            'subtotal' => $currency->convert($this->storage['base_subtotal']),
            'shipping' => $currency->convert($shipping),
        ]);
        $this->getEventDispatcher()->trigger('tax.calc', ['model' => $this]);
        $this->getEventDispatcher()->trigger('promotion.calc', ['model' => $this]);
        $this->setData([
            'base_total' => $this->storage['base_subtotal'] +
            $this->storage['base_shipping'] +
            ($this->storage['base_tax'] ?? 0) +
            ($this->storage['base_discount'] ?? 0),
            'total' => $this->storage['subtotal'] +
            $this->storage['shipping'] +
            ($this->storage['tax'] ?? 0) +
            ($this->storage['discount'] ?? 0)
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

    public function getQty($storeId = null)
    {
        $qty = 0;
        foreach ($this->getItems() as $item) {
            if (is_null($storeId) || $item->offsetGet('store_id') == $storeId) {
                $qty += $item['qty'];
            }
        }
        return $qty;
    }

    public function getWeight($storeId = null)
    {
        $weight = 0;
        foreach ($this->getItems() as $item) {
            if (is_null($storeId) || $item->offsetGet('store_id') == $storeId) {
                $weight += $item['weight'];
            }
        }
        return $weight;
    }

    public function getCoupon($storeId = null)
    {
        if (!empty($this->storage['coupon'])) {
            $coupons = json_decode($this->storage['coupon'], true);
            return !is_null($storeId) && isset($coupons[$storeId]) ? $coupons[$storeId] : $coupons;
        }
        return '';
    }

    public function getShippingMethod($storeId)
    {
        if (isset($this->storage['shipping_method'])) {
            $className = $this->getContainer()->get('config')['shipping/' . json_decode($this->storage['shipping_method'], true)[$storeId] . '/model'];
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

}
