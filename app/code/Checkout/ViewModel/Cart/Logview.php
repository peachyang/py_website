<?php

namespace Seahinet\Checkout\ViewModel\Cart;

use Seahinet\Catalog\Model\Collection\Product;
use Seahinet\Catalog\ViewModel\Product\Link;
use Seahinet\Sales\Model\Cart;
use Zend\Db\Sql\Predicate\In;
use Zend\Db\TableGateway\TableGateway;

class Logview extends Link
{

    public function getProducts()
    {
        $products_id = array_filter(explode(',', $this->getRequest()->getCookie('log_view')));
        $ids = [];
        foreach ($products_id as $item) {
            $ids[$item] = 1;
        }
        $products_model = new Product;
        $products = [];
        if (count($ids)) {
            $products_model->where(new In('id', array_keys($ids)));
            foreach ($products_id as $item) {
                foreach ($products_model as $item_product){
                    if ($item_product['id'] == $item)
                    $products[] = $item_product;
                }
            }
        } else {
            return [];
        }
        return $products;
    }

}
