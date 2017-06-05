<?php

namespace Seahinet\Catalog\Model;

use Seahinet\Catalog\Model\Collection\{
    Category as Categories,
    Product as Collection,
    Product\Option as OptionCollection,
    Warehouse as WarehouseCollection
};
use Seahinet\Catalog\Model\Product\Option as OptionModel;
use Seahinet\Catalog\Model\Warehouse;
use Seahinet\Lib\Model\Collection\Eav\Attribute as AttributeCollection;
use Seahinet\Lib\Model\Eav\{
    Entity,
    Attribute
};
use Seahinet\I18n\Model\Currency;
use Seahinet\Lib\Model\Store;
use Seahinet\Resource\Model\Resource;
use Zend\Db\Sql\Predicate\In;

class Product extends Entity
{

    use \Seahinet\Lib\Traits\Url;

    const ENTITY_TYPE = 'product';

    protected function construct()
    {
        $this->init('id', ['id', 'type_id', 'attribute_set_id', 'store_id', 'product_type_id', 'status']);
    }

    public function isVirtual()
    {
        return isset($this->storage['product_type_id']) && $this->storage['product_type_id'] == 2;
    }

    public function isNew()
    {
        $time = time();
        return !empty($this->storage['new_start']) &&
                strtotime($this->storage['new_start']) <= $time &&
                (empty($this->storage['new_end']) || strtotime($this->storage['new_end']) >= $time);
    }

    public function getOptions($constraint = [])
    {
        if ($this->getId()) {
            $options = new OptionCollection;
            $options->withLabel()
                    ->where(['product_id' => $this->getId()] + $constraint)
                    ->order('sort_order ASC');
            return $options;
        }
        return [];
    }

    public function getOption($id, $value = null)
    {
        if ($this->getId()) {
            $options = $this->getOptions(['id' => $idOrCode]);
            if ($options->count()) {
                $option = $options[0];
                if (!is_null($value)) {
                    return $option->getValue($value);
                }
                return $option;
            }
        }
        return null;
    }

    public function getStore()
    {
        if ($this->getId()) {
            $store = new Store;
            $store->load($this->storage['store_id']);
            return $store;
        }
        return null;
    }

    public function getCategories()
    {
        if ($this->getId()) {
            $category = new Categories($this->languageId);
            $tableGateway = $this->getTableGateway('product_in_category');
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

    public function getAttributes()
    {
        $result = [];
        if ($this->getId()) {
            $attributes = new AttributeCollection;
            $attributes->withLabel()->withSet()->columns(['id', 'code', 'input'])->where([
                'eav_attribute_set.id' => $this->storage['attribute_set_id']
            ])->where->notIn('code', [
                'images', 'default_image', 'thumbnail', 'uri_key',
                'description', 'short_description', 'taxable'
            ])->notLike('code', '%price%')->notLike('code', 'meta%')->notEqualTo('type', 'datetime');
            $getValue = function ($attribute, $value) {
                return in_array($attribute['input'], ['select', 'radio', 'checked', 'multiselect']) ? $attribute->getOption($value) : $value;
            };
            foreach ($attributes as $attribute) {
                if (is_array($this->storage[$attribute->offsetGet('code')])) {
                    $result[$attribute->offsetGet('label')] = '';
                    foreach ($this->storage[$attribute->offsetGet('code')] as $value) {
                        $result[$attribute->offsetGet('label')] .= $getValue($attribute, $value) . ',';
                    }
                    $result[$attribute->offsetGet('label')] = trim($result[$attribute->offsetGet('label')], ',');
                } else {
                    $result[$attribute->offsetGet('label')] = $getValue($attribute, $this->storage[$attribute->offsetGet('code')]);
                }
            }
        }
        return $result;
    }

    public function getAttribute($idOrCode, $option = null)
    {
        if ($this->getId()) {
            $attribute = new Attribute;
            $attribute->load($idOrCode, is_numeric($idOrCode) ? 'id' : 'code');
            if ($attribute->getId()) {
                if (!is_null($option)) {
                    return $attribute->getOption($option, $this->languageId);
                }
                return $attribute;
            }
        }
        return null;
    }

    public function getInventory($warehouse = null, $sku = null)
    {
        if (is_null($sku)) {
            $sku = $this->storage['sku'];
        }
        if (is_null($warehouse)) {
            $warehouses = new WarehouseCollection;
            $warehouses->where(['status' => 1]);
            $result = 0;
            foreach ($warehouses as $warehouse) {
                $inventory = $warehouse->getInventory($this->getId(), $sku);
                if ($inventory) {
                    $result += $inventory['qty'];
                }
            }
            return $result;
        } else if (is_numeric($warehouse)) {
            $warehouse = (new Warehouse)->setId($warehouse);
        }
        return $warehouse->getInventory($this->getId(), $sku);
    }

    public function getLinkedProducts($type)
    {
        if ($this->getId()) {
            $products = new Collection($this->languageId);
            $tableGateway = $this->getTableGateway('product_link');
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
        if (!count($result)) {
            return '#';
        }
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
        if (!empty($this->storage['thumbnail'])) {
            $resource = new Resource;
            $resource->load($this->storage['thumbnail']);
            return $resource['real_name'];
        }
        return $this->getPubUrl('frontend/images/placeholder.png');
    }

    public function getDefaultImage()
    {
        if (!empty($this->storage['default_image'])) {
            $resource = new Resource;
            $resource->load($this->storage['default_image']);
            return $resource['real_name'];
        }
        return $this->getPubUrl('frontend/images/placeholder.png');
    }

    public function getFinalPrice($qty = 1, $convert = true)
    {
        if (empty($this->storage['prices'])) {
            $this->storage['prices'] = [];
            $this->storage['base_prices'] = [];
            $this->getEventDispatcher()->trigger('product.price.calc', [
                'product' => $this, 'qty' => $qty
            ]);
        }
        return $convert ? min($this->storage['prices']) : min($this->storage['base_prices']);
    }

    protected function afterLoad(&$result)
    {
        if (isset($result[0]) && !empty($result[0]['images'])) {
            if (!is_array($result[0]['images'])) {
                $result[0]['images'] = json_decode($result[0]['images'], true);
            }
            $images = $result[0]['images'];
            $result[0]['images'] = [];
            foreach ($images as $item) {
                $result[0]['images'][$item['id']] = $item + ['src' => (new Resource)->load($item['id'])['real_name']];
            }
        } else if (!empty($result['images'])) {
            if (!is_array($result['images'])) {
                $result['images'] = json_decode($result['images'], true);
            }
            $images = $result['images'];
            $result['images'] = [];
            foreach ($images as $item) {
                $result['images'][$item['id']] = $item + ['src' => (new Resource)->load($item['id'])['real_name']];
            }
        }
        parent::afterLoad($result);
    }

    protected function beforeSave()
    {
        if (isset($this->storage['images']) && is_array($this->storage['images'])) {
            $images = [];
            foreach ($this->storage['images'] as $order => $id) {
                if (is_array($id)) {
                    break;
                }
                if ($id && !isset($images[$id])) {
                    $images[$id] = [
                        'id' => $id,
                        'label' => $this->storage['images-label'][$order],
                        'group' => $this->storage['images-group'][$order]
                    ];
                }
            }
            $images = array_values($images);
            if (empty($this->storage['default_image']) && !empty($images)) {
                $this->storage['default_image'] = $images[0]['id'];
            }
            if (empty($this->storage['thumbnail']) && !empty($images)) {
                $this->storage['thumbnail'] = $images[0]['id'];
            }
            $this->storage['images'] = json_encode($images);
        }
        if (isset($this->storage['additional']) && is_array($this->storage['additional'])) {
            $this->storage['additional'] = json_encode(array_combine($this->storage['additional']['key'], $this->storage['additional']['value']));
        }
        parent::beforeSave();
    }

    protected function afterSave()
    {
        if (!empty($this->storage['category'])) {
            $tableGateway = $this->getTableGateway('product_in_category');
            $tableGateway->delete(['product_id' => $this->getId()]);
            $maxCount = (int) $this->getContainer()->get('config')['catalog/product/count_in_category'];
            foreach ((array) $this->storage['category'] as $category) {
                if ($maxCount--) {
                    $tableGateway->insert(['product_id' => $this->getId(), 'category_id' => $category]);
                }
                if ($maxCount === 0) {
                    break;
                }
            }
        }
        if (!empty($this->storage['inventory'])) {
            $warehouse = new Warehouse;
            foreach ($this->storage['inventory'] as $warehouseId => $inventory) {
                foreach ($inventory['qty'] as $order => $qty) {
                    if (empty($inventory['sku'][$order]) || $inventory['sku'][$order] !== $this->storage['sku']) {
                        $warehouse->setInventory([
                            'warehouse_id' => $warehouseId,
                            'product_id' => $this->getId(),
                            'sku' => empty($inventory['sku'][$order]) ? $this->storage['sku'] : $inventory['sku'][$order],
                            'barcode' => $inventory['barcode'][$order - 1] ?? '',
                            'qty' => empty($inventory['sku'][$order]) && count($inventory['qty']) > 1 ? array_sum($inventory['qty']) - $qty : $qty,
                            'reserve_qty' => $inventory['reserve_qty'][$order] ?? ($inventory['reserve_qty'][0] ?? null),
                            'min_qty' => $inventory['min_qty'][$order] ?? ($inventory['min_qty'][0] ?? null),
                            'max_qty' => $inventory['max_qty'][$order] ?? ($inventory['max_qty'][0] ?? null),
                            'is_decimal' => $inventory['is_decimal'][$order] ?? ($inventory['is_decimal'][0] ?? null),
                            'backorders' => $inventory['backorders'][$order] ?? ($inventory['backorders'][0] ?? null),
                            'increment' => $inventory['increment'][$order] ?? ($inventory['increment'][0] ?? null),
                            'status' => $inventory['status'][$order] ?? ($inventory['status'][0] ?? null)
                        ]);
                    }
                }
            }
        }
        if (isset($this->storage['product_link'])) {
            $tableGateway = $this->getTableGateway('product_link');
            $tableGateway->delete(['product_id' => $this->getId()]);
            if (is_array($this->storage['product_link'])) {
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
        }
        if (isset($this->storage['options'])) {
            $this->getTableGateway('product_option')->delete(['product_id' => $this->getId()]);
            if (is_array($this->storage['options'])) {
                foreach ($this->storage['options']['label'] as $id => $label) {
                    $option = new OptionModel;
                    $option->setData([
                        'id' => null,
                        'product_id' => $this->getId(),
                        'label' => $label,
                        'input' => $this->storage['options']['input'][$id],
                        'is_required' => $this->storage['options']['is_required'][$id],
                        'sort_order' => $this->storage['options']['sort_order'][$id],
                        'price' => (float) $this->storage['options']['price'][$id],
                        'is_fixed' => $this->storage['options']['is_fixed'][$id],
                        'sku' => $this->storage['options']['sku'][$id],
                        'value' => $this->storage['options']['value'][$id] ?? null
                    ])->save();
                }
            }
            $this->flushList('product_option');
        }
        parent::afterSave();
    }

    public function serialize()
    {
        unset($this->storage['prices']);
        return parent::serialize();
    }

    public function getCurrency()
    {
        if (isset($this->storage['currency'])) {
            return (new Currency)->load($this->storage['currency'], 'code');
        }
        return $this->getContainer()->get('currency');
    }

    public function canSold()
    {
        return $this->storage['status'] && $this->getStore()->offsetGet('status');
    }

}
