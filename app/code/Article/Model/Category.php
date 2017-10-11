<?php

namespace Seahinet\Article\Model;

use Seahinet\Article\Model\Collection\Product as ProductCollection;
use Seahinet\Article\Model\Collection\Category as Collection;
use Seahinet\Lib\Model\Eav\Entity;
use Seahinet\Resource\Model\Resource;
use Zend\Db\Sql\Predicate\In;

class Category extends Entity
{

    use \Seahinet\Lib\Traits\Url;

    const ENTITY_TYPE = 'art_category';

    protected function construct()
    {
        $this->init('id', ['id', 'type_id', 'attribute_set_id', 'store_id', 'parent_id', 'sort_order', 'status']);
    }

    public function getProducts()
    {
        if ($this->getId()) {
            $products = new ProductCollection($this->languageId);
            $tableGateway = $this->getTableGateway('article_in_category');
            $result = $tableGateway->select(['category_id' => $this->getId()])->toArray();
            $valueSet = [];
            array_walk($result, function($item) use (&$valueSet) {
                $valueSet[] = $item['article_id'];
            });
            if (count($valueSet)) {
                $products->where(new In('id', $valueSet));
                return $products;
            }
        }
        return [];
    }

    public function getParentCategory()
    {
        if ($this->storage['parent_id']) {
            $category = new static($this->languageId);
            $category->load($this->storage['parent_id']);
            return $category->getId() ? $category : null;
        }
        return null;
    }

    public function getChildrenCategories($shownInMenu = null)
    {
        if ($this->getId()) {
            $category = new Collection($this->languageId);
            $category->where(['parent_id' => $this->getId()]);
            if (!is_null($shownInMenu)) {
                $category->where(['include_in_menu' => $shownInMenu]);
            }
            return $category;
        }
        return [];
    }

    public function getImage()
    {
        if (!empty($this->storage['image'])) {
            $resource = new Resource;
            $resource->load($this->storage['image']);
            return $resource['real_name'];
        }
        return $this->getPubUrl('frontend/images/placeholder.png');
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

    public function getUrl()
    {
        if (!isset($this->storage['path'])) {
            $constraint = ['article_id' => null, 'category_id' => $this->getId()];
            $result = $this->getContainer()->get('indexer')->select('article_url', $this->languageId, $constraint);
            $this->storage['path'] = isset($result[0]) ? $result[0]['path'] . '.html' : '';
        }
        return $this->getBaseUrl($this->storage['path']);
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
