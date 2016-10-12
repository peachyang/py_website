<?php

namespace Seahinet\Retailer\Model;

use Seahinet\Catalog\Model\Collection\Product;
use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Retailer\Model\Collection\Category as Collection;

class Category extends AbstractModel
{

    protected function construct()
    {
        $this->init('retailer_category', 'id', ['id', 'parent_id', 'default_name', 'store_id', 'uri_key']);
    }

    protected function beforeLoad($select)
    {
        $select->join('retailer_category_name', 'retailer_category_name.category_id=retailer_category.id', ['language_id', 'name'], 'left');
        parent::beforeLoad($select);
    }

    protected function afterLoad(&$result)
    {
        if (isset($result[0])) {
            $name = [];
            foreach ($result as $item) {
                $name[$item['language_id']] = $item['name'];
            }
            $result[0]['name'] = $name;
        }
        parent::afterLoad($result);
    }

    public function getName($languageId = null)
    {
        return $languageId && isset($this->storage['name'][$languageId]) ? $this->storage['name'][$languageId] : $this->storage['default_name'];
    }

    public function getProducts()
    {
        if ($this->getId()) {
            $products = new Product($this->languageId);
            $tableGateway = $this->getTableGateway('retailer_category_with_product');
            $result = $tableGateway->select(['category_id' => $this->getId()])->toArray();
            $valueSet = [];
            array_walk($result, function($item) use (&$valueSet) {
                $valueSet[] = $item['product_id'];
            });
            if (count($valueSet)) {
                $products->where(new In('id', $valueSet));
                return $products;
            }
        }
        return [];
    }

    public function getChildrenCategories()
    {
        if ($this->getId()) {
            $category = new Collection;
            $category->where(['parent_id' => $this->getId()]);
            return $category;
        }
        return [];
    }

}
