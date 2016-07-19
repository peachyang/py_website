<?php

namespace Seahinet\Catalog\Model;

use Seahinet\Catalog\Model\Collection\Product;
use Seahinet\Catalog\Model\Collection\Category as Collection;
use Seahinet\Lib\Model\Eav\Entity;
use Zend\Db\Sql\Predicate\In;
use Zend\Db\TableGateway\TableGateway;

class Category extends Entity
{

    use \Seahinet\Lib\Traits\Url;

    const ENTITY_TYPE = 'category';

    protected function construct()
    {
        $this->init('id', ['id', 'type_id', 'attribute_set_id', 'store_id', 'parent_id', 'sort_order', 'status']);
    }

    public function getProducts()
    {
        if ($this->getId()) {
            $products = new Product($this->languageId);
            $tableGateway = new TableGateway('product_in_category', $this->getContainer()->get('dbAdapter'));
            $result = $tableGateway->select(['category_id' => $this->getId()])->toArray();
            $valueSet = [];
            array_walk($result, function($item) use (&$valueSet) {
                $valueSet[] = $item['product_id'];
            });
            if (count($valueSet)) {
                $products->where(new In('id', $valueSet));
            }
            return $products;
        }
        return [];
    }

    public function getChildrenCategories()
    {
        if ($this->getId()) {
            $category = new Collection($this->languageId);
            $category->where(['parent_id' => $this->getId()]);
            return $category;
        }
        return [];
    }

    public function getUrl()
    {
        if (isset($this->storage['path'])) {
            return $this->getBaseUrl($this->storage['path']);
        }
        $constraint = ['product_id' => null, 'category_id' => $this->getId()];
        $result = $this->getContainer()->get('indexer')->select('catalog_url', $this->languageId, $constraint);
        $this->storage['path'] = $result[0]['path'] . '.html';
        return $this->getBaseUrl($result[0]['path'] . '.html');
    }

    public function beforeSave()
    {
        if (!empty($this->storage['sortable']) && is_array($this->storage['sortable'])) {
            $this->storage['sortable'] = implode(',', $this->storage['sortable']);
        }
        parent::beforeSave();
    }

    protected function afterLoad(&$result)
    {
        if (isset($result['sortable']) && is_string($result['sortable']) && strpos($result['sortable'], ',')) {
            $result['sortable'] = explode(',', $result['sortable']);
        } else if (isset($result[0]['sortable']) && is_string($result[0]['sortable']) && strpos($result[0]['sortable'], ',')) {
            $result[0]['sortable'] = explode(',', $result[0]['sortable']);
        }
        parent::afterLoad($result);
    }

}
