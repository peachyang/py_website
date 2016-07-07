<?php

namespace Seahinet\Catalog\Model;

use Seahinet\Catalog\Model\Collection\Product;
use Seahinet\Lib\Model\Eav\Entity;
use Zend\Db\TableGateway\TableGateway;

class Category extends Entity
{

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
            } else {
                return [];
            }
            return $products;
        }
        return [];
    }

}
