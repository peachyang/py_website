<?php

namespace Seahinet\Catalog\ViewModel\Product;

class Upsells extends Link
{

    public function getProducts()
    {
        $products = $this->getVariable('product')->getLinkedProducts('u');
        if ($this->getLimit() && is_object($products)) {
            $products->limit($this->getLimit());
        }
        return $products;
    }

}