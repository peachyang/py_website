<?php

namespace Seahinet\Sales\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\I18n\Model\Currency;
use Seahinet\Sales\Model\Collection\Shipment\Item as ItemCollection;
use Seahinet\Sales\Model\Collection\Shipment\ItemTrack;

class Shipment extends AbstractModel
{

    protected $items = null;

    protected function construct()
    {
        $this->init('sales_order_shipment', 'id', [
            'id', 'order_id', 'increment_id', 'customer_id', 'store_id',
            'shipping_method', 'billing_address_id', 'shipping_address_id',
            'warehouse_id', 'store_id', 'billing_address', 'shipping_address',
            'comment', 'status'
        ]);
    }

    public function getItems($force = false)
    {
        if ($force || is_null($this->items)) {
            $items = new ItemCollection();
            $items->where(['shipment_id' => $this->getId()]);
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

    public function getOrder()
    {
        return isset($this->storage['order_id']) ?
                (new Order)->load($this->storage['order_id']) : null;
    }

    public function getShippingMethod()
    {
        if (is_null($this->items)) {
            $itemTrack = new ItemTrack;
            $itemTrack->where(['shipment_id' => $this->getId()]);
            return $itemTrack;
        }
        return [];
    }

}
