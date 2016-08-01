<?php

namespace Seahinet\Sales\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Sales\Model\Order\Item;

class Order extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_order', 'id', [
            'id', 'status_id', 'increment_id', 'customer_id',
            'billing_address_id', 'shipping_address_id', 'warehouse_id',
            'store_id', 'billing_address', 'shipping_address', 'coupon',
            'is_virtual', 'free_shipping', 'base_currency', 'currency',
            'shipping_method', 'payment_method', 'base_shipping', 'shipping',
            'base_discount', 'discount', 'discount_detail', 'base_tax', 'tax', 'base_total', 'total',
            'base_total_paid', 'total_paid', 'additional', 'customer_note'
        ]);
    }

    public function place($warehouseId, $storeId)
    {
        $cart = Cart::instance();
        $note = json_decode($cart->toArray()['customer_note'], true);
        $this->setData($cart->toArray())
                ->setData([
                    'shipping_method' => json_decode($cart->toArray()['shipping_method'], true)[$storeId],
                    'customer_note' => isset($note[$storeId]) ? $note[$storeId] : '',
                    'warehouse_id' => $warehouseId,
                    'store_id' => $storeId,
                    'status_id' => 1
                ])->setId(null)->collateTotals()->save();
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
        return $this;
    }

    public function collateTotals()
    {
        return $this;
    }

}
