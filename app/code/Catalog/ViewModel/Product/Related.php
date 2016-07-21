<?php

namespace Seahinet\Catalog\ViewModel\Product;

class Related extends Link
{

    public function getProducts()
    {
        $products = $this->getVariable('product')->getLinkedProducts('r');
        if ($this->getLimit()) {
            $products->limit($this->getLimit());
        }
        return $products;
    }
}
