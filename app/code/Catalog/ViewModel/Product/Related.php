<?php

namespace Seahinet\Catalog\ViewModel\Product;

class Related extends Link
{

    public function getProducts()
    {
        $products = $this->getVariable('product')->getLinkedProducts('r');
        if ($this->getLimit() && is_object($products)) {
            $products->limit($this->getLimit());
        }
        return $products;
    }

}
