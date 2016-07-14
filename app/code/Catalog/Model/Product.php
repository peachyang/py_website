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

    use \Seahinet\Lib\Traits\Url;

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

    public function getUrl($category = null)
    {
        $constraint = ['product_id' => $this->getId()];
        if (is_object($category) || is_array($category)) {
            $constraint['category_id'] = $category['id'];
        }
        if (is_null($category) && isset($this->storage['path'][0])) {
            return $this->getBaseUrl($this->storage['path'][0]);
        } else if (isset($constraint['category_id']) && isset($this->storage['path'][$constraint['category_id']])) {
            return $this->getBaseUrl($this->storage['path'][$constraint['category_id']]);
        }
        $result = $this->getContainer()->get('indexer')->select('catalog_url', $this->languageId, $constraint);
        if (is_null($category)) {
            $this->storage['path'][0] = $result[0]['path'] . '.html';
        } else if (isset($constraint['category_id'])) {
            $this->storage['path'][$constraint['category_id']] = $result[0]['path'] . '.html';
        } else {
            $this->storage['path'][$result[0]['category_id']] = $result[0]['path'] . '.html';
        }
        return $this->getBaseUrl($result[0]['path'] . '.html');
    }

    public function getThumbnail()
    {
        if ($this->storage['thumbnail']) {
            $resource = new Resource;
            $resource->load($this->storage['thumbnail']);
            return $this->getBaseUrl('pub/resource/images/' . $resource['real_name']);
        }
        return $this->getPubUrl('frontend/images/placeholder.png');
    }

    public function getFinalPrice($qty = 1)
    {
        if (empty($this->storage['prices'])) {
            $this->storage['prices'] = [];
            $this->getEventDispatcher()->trigger('product.price.calc', [
                'product' => $this, 'qty' => $qty
            ]);
        }
        return min($this->storage['prices']);
    }

    protected function afterLoad(&$result)
    {
        if (isset($result[0]) && !empty($result[0]['images'])) {
            $result[0]['images'] = json_decode($result[0]['images'], true);
            foreach ($result[0]['images'] as &$item) {
                $item['src'] = (new Resource)->load($item['id'])['real_name'];
            }
        } else if (!empty($result['images'])) {
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
                        'sku' => isset($inventory['sku'][$order]) ? $inventory['sku'][$order] : $this->storage['sku'],
                        'barcode' => isset($inventory['barcode'][$order]) ? $inventory['barcode'][$order] : '',
                        'qty' => $qty,
                        'reserve_qty' => isset($inventory['reserve_qty'][$order]) ? $inventory['reserve_qty'][$order] : null,
                        'min_qty' => isset($inventory['min_qty'][$order]) ? $inventory['min_qty'][$order] : null,
                        'max_qty' => isset($inventory['max_qty'][$order]) ? $inventory['max_qty'][$order] : null,
                        'is_decimal' => isset($inventory['is_decimal'][$order]) ? $inventory['is_decimal'][$order] : null,
                        'backorders' => isset($inventory['backorders'][$order]) ? $inventory['backorders'][$order] : null,
                        'increment' => isset($inventory['increment'][$order]) ? $inventory['increment'][$order] : null,
                        'status' => isset($inventory['status'][$order]) ? $inventory['status'][$order] : null
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
                    'value' => isset($this->storage['options']['value'][$id]) ? $this->storage['options']['value'][$id] : null
                ])->save();
            }
        }
        parent::afterSave();
    }

}
