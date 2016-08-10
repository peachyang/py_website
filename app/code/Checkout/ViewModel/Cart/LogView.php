<?php

namespace Seahinet\Checkout\ViewModel\Cart;

use Seahinet\Catalog\Model\Collection\Product;
use Seahinet\Catalog\ViewModel\Product\Link;
use Seahinet\Sales\Model\Cart;
use Zend\Db\Sql\Predicate\In;

class LogView extends Link
{

    protected $products = null;

    public function getProducts()
    {
        if (is_null($this->products)) {
            $ids = [];
            foreach (Cart::instance()->getItems() as $item) {
                $ids[] = $item['product_id'];
            }
            $ids = array_diff($ids, explode(',', $this->getRequest()->getCookie('log_view')));
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
