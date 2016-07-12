<?php

namespace Seahinet\Catalog\ViewModel\Category;

use Seahinet\Lib\ViewModel\Template;

class ProductList extends Template
{

    protected $products = null;

    public function getProducts()
    {
        return $this->products;
    }

    public function setProducts($products)
    {
        $this->products = $products;
        return $this;
    }

}
