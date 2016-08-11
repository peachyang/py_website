<?php

namespace Seahinet\Catalog\Listeners;

use Seahinet\Catalog\Exception\OutOfStock;
use Seahinet\Catalog\Model\Warehouse;
use Seahinet\Lib\Listeners\ListenerInterface;

class Inventory implements ListenerInterface
{

    public function check($event)
    {
        $warehouse = new Warehouse;
        $warehouse->load($event['warehouse_id']);
        $inventory = $warehouse->getInventory($event['product_id'], $event['sku']);
        $left = $inventory['qty'] - $inventory['reserve_qty'];
        if (!$inventory['status'] || $event['qty'] > $left) {
            throw new OutOfStock('There are only ' . $left .
            ' left in stock. (Product SKU: ' . $event['sku'] . ')');
        }
    }

    public function decrease($event)
    {
        $model = $event['model'];
        if ($model->getId()) {
            return;
        }
        $warehouse = new Warehouse;
        $warehouse->load($model['warehouse_id']);
        foreach ($model->getItems() as $item) {
            if (is_object($item)) {
                $item = $item->toArray();
            }
            $this->check([
                'warehouse_id' => $model['warehouse_id'],
                'product_id' => $item['product_id'],
                'sku' => $item['sku']
            ]);
            $inventory = $warehouse->getInventory($item['product_id'], $item['sku']);
            $inventory['qty'] = $inventory['qty'] - $item['qty'];
            $inventory['id'] = null;
            $warehouse->setInventory($inventory);
        }
    }

    public function increase($event)
    {
        $model = $event['model'];
        if ($model->getId()) {
            return;
        }
        $warehouse = new Warehouse;
        $warehouse->load($model['warehouse_id']);
        foreach ($model->getItems() as $item) {
            if (is_object($item)) {
                $item = $item->toArray();
            }
            $this->check([
                'warehouse_id' => $model['warehouse_id'],
                'product_id' => $item['product_id'],
                'sku' => $item['sku']
            ]);
            $inventory = $warehouse->getInventory($item['product_id'], $item['sku']);
            $inventory['qty'] = $inventory['qty'] + $item['qty'];
            $inventory['id'] = null;
            $warehouse->setInventory($inventory);
        }
    }

}
