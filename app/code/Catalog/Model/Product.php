<?php

namespace Seahinet\Catalog\Model;

use Seahinet\Catalog\Model\Collection\Category as Categories;
use Seahinet\Catalog\Model\Collection\Product as Collection;
use Seahinet\Catalog\Model\Collection\Product\Option as OptionCollection;
use Seahinet\Catalog\Model\Product\Option as OptionModel;
use Seahinet\Catalog\Model\Warehouse;
use Seahinet\Resource\Model\Resource;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Model\Eav\Entity;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate\In;
use Seahinet\Catalog\Model\Collection\Product\Review;
use Seahinet\Lib\Session\Segment;
use Seahinet\Customer\Model\Customer;
use Seahinet\I18n\Model\Currency;

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
        return isset($this->storage['product_type_id']) && $this->storage['product_type_id'] === 2;
    }

    public function getOptions($constraint = [])
    {
        if ($this->getId()) {
            $options = new OptionCollection;
            $options->withLabel()
                    ->where(['product_id' => $this->getId()] + $constraint);
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

    public function getAttributes()
    {
        $result = [];
        if ($this->getId()) {
            $attributes = new Attribute;
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

    public function getInventory($warehouse, $sku = null)
    {
        if (is_null($sku)) {
            $sku = $this->storage['sku'];
        }
        if (is_numeric($warehouse)) {
            $warehouse = (new Warehouse)->setId($warehouse);
        }
        return $warehouse->getInventory($this->getId(), $sku);
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
        if (!empty($this->storage['thumbnail'])) {
            $resource = new Resource;
            $resource->load($this->storage['thumbnail']);
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
            foreach ($result[0]['images'] as &$item) {
                $item['src'] = (new Resource)->load($item['id'])['real_name'];
            }
        } else if (!empty($result['images'])) {
            if (!is_array($result['images'])) {
                $result['images'] = json_decode($result['images'], true);
            }
            foreach ($result['images'] as &$item) {
                $item['src'] = (new Resource)->load($item['id'])['real_name'];
            }
        }
        parent::afterLoad($result);
    }

    protected function beforeSave()
    {
        if (isset($this->storage['images']) && is_array($this->storage['images'])) {
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
        if (isset($this->storage['additional']) && is_array($this->storage['additional'])) {
            $this->storage['additional'] = json_encode(array_combine($this->storage['additional']['key'], $this->storage['additional']['value']));
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
                        'sku' => empty($inventory['sku'][$order]) ? $this->storage['sku'] : $inventory['sku'][$order],
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
                    'price' => (float) $this->storage['options']['price'][$id],
                    'is_fixed' => $this->storage['options']['is_fixed'][$id],
                    'sku' => $this->storage['options']['sku'][$id],
                    'value' => isset($this->storage['options']['value'][$id]) ? $this->storage['options']['value'][$id] : null
                ])->save();
            }
        }
        parent::afterSave();
    }

    public function serialize()
    {
        unset($this->storage['prices']);
        return parent::serialize();
    }

    public function getReviews()
    {
        $result = [];
        if ($this->getId()) {
            $reviews = new Review;
            $reviews->where(['product_id' => $this->getId()]);
            $reviews->join('review_rating', 'review.id=review_rating.review_id', ['review_id', 'rating_id', 'value'], 'left')
                    ->join('rating', 'review_rating.rating_id=rating.id', ['title'], 'left');
            $reviews = $reviews->toArray();
            foreach ($reviews as $key => $value) {
                if ($key > 0) {
                    if ($reviews[$key]['id'] == $reviews[$key - 1]['id']) {
                        $reviews[$key] = array_merge_recursive($reviews[$key]->toArray(), $reviews[$key - 1]->toArray());
                        unset($reviews[$key - 1]);
                    }
                }
            }
            $customer = new Customer();
            foreach ($reviews as $key => $value) {
                if (!is_null($value['customer_id'])) {
                    $reviews[$key]['username'] = $customer->load($value['customer_id'])['username'];
                }
            }
        } else {
            return [];
        }
        return $reviews;
    }

    public function getCustomerID()
    {
        $segment = new Segment('customer');
        if ($segment->get('hasLoggedIn')) {
            return $segment->get('customer')['id'];
        }
        return false;
    }

    public function getCurrency()
    {
        if (isset($this->storage['currency'])) {
            return (new Currency)->load($this->storage['currency'], 'code');
        }
        return $this->getContainer()->get('currency');
    }

}
