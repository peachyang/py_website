<?php

namespace Seahinet\Sales\Model;

use Exception;
use Seahinet\Catalog\Model\Product;
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
            'id', 'customer_id', 'status', 'additional', 'customer_note',
            'billing_address_id', 'shipping_address_id', 'warehouse_id',
            'store_id', 'billing_address', 'shipping_address', 'coupon',
            'is_virtual', 'free_shipping', 'base_currency', 'currency',
            'shipping_method', 'payment_method', 'base_shipping', 'shipping',
            'base_discount', 'discount', 'base_tax', 'tax', 'base_total', 'total'
        ]);
    }

    protected function init($table, $primaryKey = 'id', $columns = array())
    {
        parent::init($table, $primaryKey, $columns);
        $segment = new Segment('customer');
        $baseCurrency = $this->getContainer()->get('config')['i18n/currency/default'];
        $currency = $this->getContainer()->get('request')->getCookie('currency', $baseCurrency);
        if ($segment->get('cart')) {
            $this->load($segment->get('cart'));
        } else if ($segment->get('isLoggedin')) {
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
        }
        return static::$instance;
    }

    public function abandon()
    {
        static::$instance->setData('status', 0)->save();
        static::$instance = $this->regenerate();
    }

    public function combine($cart)
    {
        try {
            $this->beginTransaction();
            $id = $this->getId();
            $cart->setData('status', 0)->save();
            foreach ($cart->getItems() as $item) {
                $item = new Item($item);
                $item->setData([
                    'id' => null,
                    'cart_id' => $id
                ])->save();
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            $this->getContainer()->get('log')->logException($e);
        }
        static::$instance = $this;
    }

    private function regenerate($cart = null, $currency = null, $baseCurrency = null)
    {
        $segment = new Segment('customer');
        if (is_null($baseCurrency)) {
            $baseCurrency = $this->getContainer()->get('config')['i18n/currency/default'];
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
        if ($segment->get('isLoggedin')) {
            $cart->setData([
                'currency' => $currency
            ]);
        }
        $cart->save();
        $segment->set('cart', $cart->getId());
        return $cart;
    }

    public function getItems()
    {
        if (is_null($this->items)) {
            $items = new ItemCollection();
            $items->where(['cart_id' => $this->getId()]);
            $result = [];
            $items->walk(function($item) use (&$result) {
                $result[$item['id']] = $item;
            });
            $this->items = $result;
        }
        return $this->items;
    }

    public function addItem($productId, $qty, $warehouseId, array $options = [], $sku = '')
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
            'sku' => $sku,
            'options' => json_encode($options)
        ]);
        $item = new Item;
        if ($items->count()) {
            $newQty = $items[0]['qty'] + $qty;
            $price = $product->getFinalPrice($newQty);
            $item->setData($items[0])
                    ->setData([
                        'qty' => $newQty,
                        'price' => $price
                    ])->collateTotals()->save();
            $this->items[$items[0]['id']]['qty'] = $newQty;
        } else {
            $price = $product->getFinalPrice($qty);
            $item->setData([
                'product_id' => $productId,
                'product_name' => $product['name'],
                'qty' => $qty,
                'options' => json_encode($options),
                'sku' => $sku,
                'is_virtual' => $product['product_type_id'] == 2 ? 1 : 0,
                'warehouse_id' => $warehouseId,
                'weight' => $product['weight'],
                'price' => $price
            ])->collateTotals()->save();
            $this->items[$item->getId()] = $item->toArray();
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

    public function removeItem($item)
    {
        if (is_numeric($item)) {
            unset($this->items[$item]);
            $item = new Item;
            $item->load($item);
        } else {
            unset($this->items[$item['id']]);
        }
        $this->getEventDispatcher()->trigger('cart.remove.after', [
            'product_id' => $item['product_id'],
            'qty' => $item['qty'],
            'warehouse_id' => $item['warehouse_id'],
            'sku' => $item['sku']
        ]);
        $item->remove();
        return $this;
    }

    public function convertPrice($currency)
    {
        try {
            $this->beginTransaction();
            if (is_string($currency)) {
                $currency = new Currency;
                $currency->load($currency, 'code');
            }
            foreach (['shipping', 'tax', 'discount', 'total'] as $attr) {
                $this->storage[$attr] = $currency->convert($this->storage[$attr]);
            }
            foreach ($this->getItems() as $item) {
                $item = new Item($item);
                foreach (['price', 'tax', 'discount', 'total'] as $attr) {
                    $item->offsetSet($attr, $currency->convert($item->offsetGet($attr)));
                }
                $item->save();
            };
            $this->storage['currency'] = $currency->offsetGet('code');
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
                $this->storage[$attr] = $currency->convert($this->storage[$attr]);
            }
            foreach ($this->getItems() as $item) {
                $item = new Item($item);
                foreach (['base_price', 'base_tax', 'base_discount', 'base_total'] as $attr) {
                    $item->offsetSet($attr, $currency->convert($item->offsetGet($attr)));
                }
                $item->save();
            };
            $this->storage['base_currency'] = $currency->offsetGet('code');
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            $this->getContainer()->get('log')->logException($e);
        }
    }

}
