<?php

namespace Seahinet\Catalog\Model;

use Seahinet\Lib\Model\AbstractModel;
use Zend\Db\TableGateway\TableGateway;

class Warehouse extends AbstractModel
{

    protected function construct()
    {
        $this->init('warehouse', 'id', ['name', 'country', 'region', 'city', 'address', 'contact_info', 'longitude', 'latitude', 'open_at', 'close_at', 'status']);
    }

    public function getInventory($productId, $sku = '')
    {
        if ($this->getId()) {
            $cache = $this->getContainer()->get('cache');
            $key = $this->getId() . '_' . $productId . '_' . $sku;
            $result = $cache->fetch($key, 'INVENTORY_');
            if (!$result) {
                $tableGateway = new TableGateway('warehouse_inventory', $this->getContainer()->get('dbAdapter'));
                $result = $tableGateway->select([
                            'warehouse_id' => $this->getId(),
                            'product_id' => $productId,
                            'sku' => $sku
                        ])->toArray();
                if(count($result)){
                    $result = $result[0];
                }
                $cache->save($key, $result, 'INVENTORY_');
            }
            return $result;
        }
        return null;
    }

    public function setInventory(array $inventory)
    {
        if ($this->getId() || isset($inventory['warehouse_id'])) {
            $cache = $this->getContainer()->get('cache');
            $id = $this->getId()? : $inventory['warehouse_id'];
            $tableGateway = new TableGateway('warehouse_inventory', $this->getContainer()->get('dbAdapter'));
            $this->upsert($inventory, [
                'warehouse_id' => $this->getId()? : $inventory['warehouse_id'],
                'product_id' => $inventory['product_id'],
                'sku' => $inventory['sku']
                    ], $tableGateway);
            $cache->save($id . '_' . $inventory['product_id'] . '_' . $inventory['sku'], $inventory + ['warehouse_id' => $this->getId()], 'INVENTORY_');
            $this->flushList('warehouse');
        }
        return $this;
    }

}
