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
        $warehouse = new Warehouse;
        $warehouse->load($event['warehouse_id']);
        $inventory = $warehouse->getInventory($event['product_id'], $event['sku']);
        $inventory['qty'] = $inventory['qty'] - $event['qty'];
        $inventory['id'] = null;
        $warehouse->setInventory($inventory);
    }

    public function increase($event)
    {
        $warehouse = new Warehouse;
        $warehouse->load($event['warehouse_id']);
        $inventory = $warehouse->getInventory($event['product_id'], $event['sku']);
        $inventory['qty'] = $inventory['qty'] + $event['qty'];
        $inventory['id'] = null;
        $warehouse->setInventory($inventory);
    }

}
