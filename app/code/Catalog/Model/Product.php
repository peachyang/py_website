<?php

namespace Seahinet\Catalog\Model;

use Seahinet\Catalog\Model\Collection\Product\Option;
use Seahinet\Catalog\Model\Warehouse;
use Seahinet\Lib\Model\Eav\Entity;
use Zend\Db\TableGateway\TableGateway;

class Product extends Entity
{

    const ENTITY_TYPE = 'product';

    protected function construct()
    {
        $this->init('id', ['id', 'type_id', 'attribute_set_id', 'store_id', 'sku', 'product_type_id', 'status']);
    }

    public function getOptions()
    {
        $options = new Option;
        $options->withLabel()
                ->withPrice()
                ->where(['product_id' => $this->getId()]);
        return $options;
    }

    protected function afterSave()
    {
        $adapter = $this->getContainer()->get('dbAdapter');
        if (!empty($this->storage['category'])) {
            $tableGateway = new TableGateway('product_in_category', $adapter);
            $tableGateway->delete(['product_id' => $this->getId()]);
            foreach ((array) $this->storage['category'] as $category) {
                $tableGateway->insert(['product_id' => $this->getId(), 'category_id' => $category]);
            }
        }
        if (!empty($this->storage['inventory'])) {
            $warehouse = new Warehouse;
            foreach ($this->storage['inventory']['qty'] as $warehouse => $qty) {
                $warehouse->setInventory([
                    'warehouse_id' => $warehouse,
                    'product_id' => $this->getId(),
                    'sku' => '',
                    'qty' => $qty,
                    'reserve_qty' => $this->storage['inventory']['reserve_qty'][$warehouse],
                    'min_qty' => $this->storage['inventory']['min_qty'][$warehouse],
                    'max_qty' => $this->storage['inventory']['max_qty'][$warehouse],
                    'is_decimal' => $this->storage['inventory']['is_decimal'][$warehouse],
                    'backorders' => $this->storage['inventory']['backorders'][$warehouse],
                    'increment' => $this->storage['inventory']['increment'][$warehouse],
                    'status' => $this->storage['inventory']['status'][$warehouse]
                ]);
            }
        }
        if (!empty($this->storage['product_link'])) {
            $tableGateway = new TableGateway('product_link', $adapter);
            $tableGateway->delete(['product_id' => $this->getId()]);
            foreach ($this->storage['product_link'] as $type => $link) {
                foreach ($link as $order => $id) {
                    $tableGateway->insert([
                        'product_id' => $this->getId(),
                        'linked_product_id' => $id,
                        'type' => $type,
                        'sort_order' => $order
                    ]);
                }
            }
        }
        parent::afterSave();
    }

}
