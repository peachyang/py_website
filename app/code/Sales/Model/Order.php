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
            'base_discount', 'discount', 'base_tax', 'tax', 'base_total', 'total',
            'base_total_paid', 'total_paid', 'additional', 'customer_note'
        ]);
    }

    public function place($warehouseId, $storeId)
    {
        $cart = Cart::instance();
        $this->setData($cart->toArray())
                ->setData([
                    'warehouse_id' => $warehouseId,
                    'store_id' => $storeId
        ])->collateTotals()->save();
        $cart->getItems(true)->walk(function($item){
            $item = new Item($item);
            $item->setId(null)->save();
        });
    }

}
