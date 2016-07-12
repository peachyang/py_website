<?php

namespace Seahinet\Catalog\Model;

use Seahinet\Catalog\Model\Collection\Category as Categories;
use Seahinet\Catalog\Model\Collection\Product as Collection;
use Seahinet\Catalog\Model\Collection\Product\Option as OptionCollection;
use Seahinet\Catalog\Model\Product\Option as OptionModel;
use Seahinet\Catalog\Model\Warehouse;
use Seahinet\Resource\Model\Resource;
use Seahinet\Lib\Model\Eav\Entity;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate\In;

class Product extends Entity
{

    const ENTITY_TYPE = 'product';

    protected function construct()
    {
        $this->init('id', ['id', 'type_id', 'attribute_set_id', 'store_id', 'product_type_id', 'status']);
    }

    public function getOptions()
    {
        if ($this->getId()) {
            $options = new OptionCollection;
            $options->withLabel()
                    ->where(['product_id' => $this->getId()]);
            return $options;
        }
        return [];
    }

    public function getCategories()
    {
        if ($this->getId()) {
            $category = new Categories($this->languageId);
            $tableGateway = new TableGateway('product_in_category', $this->getContainer()->get('dbAdapter'));
            $result = $tableGateway->select(['product_id' => $this->getId()])->toArray();
            $valueSet = [];
            array_walk($result, function($item) use (&$valueSet) {
                $valueSet[] = $item['category_id'];
            });
            if (count($valueSet)) {
                $category->where(new In('id', $valueSet));
            } else {
                return [];
            }
            return $category;
        }
        return [];
    }

    public function getLinkedProducts($type)
    {
        if ($this->getId()) {
            $products = new Collection($this->languageId);
            $tableGateway = new TableGateway('product_link', $this->getContainer()->get('dbAdapter'));
            $result = $tableGateway->select(['product_id' => $this->getId(), 'type' => substr($type, 0, 1)])->toArray();
            $valueSet = [];
            array_walk($result, function($item) use (&$valueSet) {
                $valueSet[] = $item['linked_product_id'];
            });
            if (count($valueSet)) {
                $products->where(new In('id', $valueSet));
            } else {
                return [];
            }
            return $products;
        }
        return [];
    }

    public function getRelatedProducts()
    {
        return $this->getLinkedProducts('r');
    }

    public function getUpSells()
    {
        return $this->getLinkedProducts('u');
    }

    public function getCrossSells()
    {
        return $this->getLinkedProducts('c');
    }

    protected function afterLoad($result = array())
    {
        if (!empty($result['images'])) {
            $result['images'] = json_decode($result['images'], true);
            foreach ($result['images'] as &$item) {
                $item['src'] = (new Resource)->load($item['id'])['real_name'];
            }
        }
        parent::afterLoad($result);
    }

    protected function beforeSave()
    {
        if (is_array($this->storage['images'])) {
            $images = [];
            foreach ($this->storage['images'] as $order => $id) {
                if ($id) {
                    $images[] = [
                        'id' => $id,
                        'label' => $this->storage['images-label'][$order],
                        'group' => $this->storage['images-group'][$order]
                    ];
                }
            }
            $this->storage['images'] = json_encode($images);
        }
        parent::beforeSave();
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
            foreach ($this->storage['inventory'] as $warehouseId => $inventory) {
                foreach ($inventory['qty'] as $order => $qty) {
                    $warehouse->setInventory([
                        'warehouse_id' => $warehouseId,
                        'product_id' => $this->getId(),
                        'sku' => $inventory['sku'][$order],
                        'barcode' => isset($inventory['barcode'][$order]) ? $inventory['barcode'][$order] : '',
                        'qty' => $qty,
                        'reserve_qty' => $inventory['reserve_qty'][$order],
                        'min_qty' => $inventory['min_qty'][$order],
                        'max_qty' => $inventory['max_qty'][$order],
                        'is_decimal' => $inventory['is_decimal'][$order],
                        'backorders' => $inventory['backorders'][$order],
                        'increment' => $inventory['increment'][$order],
                        'status' => $inventory['status'][$order]
                    ]);
                }
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
                        'type' => substr($type, 0, 1),
                        'sort_order' => $order
                    ]);
                }
            }
        }
        if (!empty($this->storage['options'])) {
            $option = new OptionModel;
            foreach ($this->storage['options']['label'] as $id => $label) {
                $option->setData([
                    'id' => $id < 1 ? null : $id,
                    'product_id' => $this->getId(),
                    'label' => $label,
                    'input' => $this->storage['options']['input'][$id],
                    'is_required' => $this->storage['options']['is_required'][$id],
                    'sort_order' => $this->storage['options']['sort_order'][$id],
                    'price' => $this->storage['options']['price'][$id],
                    'is_fixed' => $this->storage['options']['is_fixed'][$id],
                    'sku' => $this->storage['options']['sku'][$id],
                    'value' => $this->storage['options']['value'][$id]
                ])->save();
            }
        }
        parent::afterSave();
    }

}
