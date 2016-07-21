<?php

namespace Seahinet\Checkout\ViewModel\Cart;

use Seahinet\Catalog\Model\Collection\Product;
use Seahinet\Catalog\ViewModel\Product\Link;
use Seahinet\Sales\Model\Cart;
use Zend\Db\Sql\Predicate\In;
use Zend\Db\TableGateway\TableGateway;

class Crosssell extends Link
{

    public function getProducts()
    {
        $tableGateway = new TableGateway('product_link', $this->getContainer()->get('dbAdapter'));
        $select = $tableGateway->getSql()->select();
        $select->columns(['linked_product_id'])->where
                ->equalTo('type', 'c')
                ->in('product_id', array_keys(Cart::instance()->getItems()));
        $result = $tableGateway->selectWith($select);
        $ids = [];
        foreach ($result as $item) {
            $ids[$item['linked_product_id']] = 1;
        }
        $products = new Product;
        if (count($ids)) {
            $products->where(new In('id', array_keys($ids)));
        } else {
            return [];
        }
        return $products;
    }

}
