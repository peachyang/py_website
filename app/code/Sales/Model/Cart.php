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
            'id', 'customer_id', 'status', 'additional', 'customer_note', 'discount_detail',
            'billing_address_id', 'shipping_address_id', 'billing_address', 'shipping_address',
            'is_virtual', 'free_shipping', 'base_currency', 'currency',
            'shipping_method', 'payment_method', 'base_shipping', 'shipping',
            'base_discount', 'discount', 'base_tax', 'tax', 'base_total', 'total'
        ]);
    }

    protected function init($table, $primaryKey = 'id', $columns = array())
    {
        parent::init($table, $primaryKey, $columns);
        $segment = new Segment('customer');
        $baseCurrency = $this->getContainer()->get('config')['i18n/currency/base'];
        $currency = $this->getContainer()->get('request')->getCookie('currency', $baseCurrency);
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
                'currency' => $currency
            ]);
        }
        $cart->save();
        $segment->set('cart', $cart->getId());
        return $cart;
    }

    public function getItem($id)
    {
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
            if ($force) {
                return $items;
            }
            $result = [];
            $items->walk(function($item) use (&$result) {
                $result[$item['id']] = $item;
                $result[$item['id']]['product'] = new Product;
                $result[$item['id']]['product']->load($item['product_id']);
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
                'options' => json_encode($options),
                'sku' => $sku,
                'is_virtual' => $product['product_type_id'] == 2 ? 1 : 0,
                'warehouse_id' => $warehouseId,
                'weight' => $product['weight'],
                'base_price' => $product->getFinalPrice($qty, false),
                'price' => $product->getFinalPrice($qty)
            ])->collateTotals()->save();
        }
        $this->items[$item->getId()] = $item->toArray();
        $this->collateTotals()->save();
        return $this;
    }

    public function changeQty($item, $qty)
    {
        if (is_numeric($item)) {
            $item = new Item;
            $item->load($item);
        }
        if ($item['qty'] > $qty && $item['min_qty'] <= $qty || $item['qty'] < $qty && $item['max_qty'] >= $qty) {
            $this->getEventDispatcher()->trigger('cart.add.before', [
                'product_id' => $item['product_id'],
                'qty' => $qty,
                'warehouse_id' => $item['warehouse_id'],
                'sku' => $item['sku'],
                'options' => $item['options']
            ]);
            $item->setData('qty', $qty)->collateTotals()->save();
            $this->items[$item->getId()]['qty'] = $item->toArray();
        }
        $this->collateTotals()->save();
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
        $this->collateTotals()->save();
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
            $this->collateTotals()->save();
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
        $this->collateTotals()->save();
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
                    $item->offsetSet($attr, $currency->convert($item->offsetGet($attr)));
                }
                $item->save();
            };
            foreach (['shipping', 'tax', 'discount', 'total'] as $attr) {
                $this->storage[$attr] = $currency->convert($this->storage[$attr]);
            }
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

    public function collateTotals()
    {
        $baseTotal = 0;
        $total = 0;
        foreach ($this->getItems(true) as $item) {
            $baseTotal += $item['base_total'];
            $total += $item['total'];
        }
        $this->setData(['base_total' => $baseTotal, 'total' => $baseTotal]);
        return $this;
    }

}
