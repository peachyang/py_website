<?php

namespace Seahinet\Checkout\ViewModel\Cart;

use Seahinet\Catalog\Model\Collection\Product;
use Seahinet\Catalog\ViewModel\Product\Link;
use Seahinet\Sales\Model\Cart;
use Zend\Db\Sql\Predicate\In;
use Zend\Db\TableGateway\TableGateway;

class Crosssell extends Link
{

    protected $products = null;

    public function getProducts()
    {
        if (is_null($this->products)) {
            $ids = [];
            foreach (Cart::instance()->getItems() as $item) {
                $ids[] = $item['product_id'];
            }
            if (!count($ids)) {
                return [];
            }
            $tableGateway = new TableGateway('product_link', $this->getContainer()->get('dbAdapter'));
            $select = $tableGateway->getSql()->select();
            $select->columns(['linked_product_id'])
                    ->where
                    ->equalTo('type', 'c')
                    ->in('product_id', $ids)
                    ->notIn('linked_product_id', $ids);
            $result = $tableGateway->selectWith($select);
            $ids = [];
            foreach ($result as $item) {
                $ids[$item['linked_product_id']] = 1;
            }
            if (count($ids)) {
                $products = new Product;
                $products->where(new In('id', array_keys($ids)));
                $this->products = $products;
            } else {
                $this->products = [];
            }
        }
        return $this->products;
    }

}
