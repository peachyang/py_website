<?php

namespace Seahinet\Checkout\ViewModel\Cart;

use Seahinet\Catalog\Model\Collection\Product;
use Seahinet\Catalog\ViewModel\Product\Link;
use Seahinet\Sales\Model\Cart;

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
            $ids = array_diff(explode(',', trim($this->getRequest()->getCookie('viewed_product'), ',')), $ids);
            if (count($ids)) {
                $products = new Product;
                $products->where(['status' => 1])->where->in('id', $ids);
                $this->products = $products;
            } else {
                $this->products = [];
            }
        }
        return $this->products;
    }

}
