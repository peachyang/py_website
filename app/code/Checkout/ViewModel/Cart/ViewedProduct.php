<?php

namespace Seahinet\Checkout\ViewModel\Cart;

use Seahinet\Catalog\Model\Collection\Product;
use Seahinet\Catalog\ViewModel\Product\Link;
use Seahinet\Sales\Model\Cart;
use Zend\Db\Sql\Predicate\In;

class ViewedProduct extends Link
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
            $ids = array_diff(explode(',', trim($this->getRequest()->getCookie('log_view'), ',')), $ids);
            if (count($ids)) {
                $products = new Product;
                $products->where(new In('id', $ids));
                $this->products = $products;
            } else {
                $this->products = [];
            }
        }
        return $this->products;
    }

}
