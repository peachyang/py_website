<?php

namespace Seahinet\Catalog\ViewModel\Product;

use Seahinet\Lib\ViewModel\Template;

class View extends Template
{

    protected $product = null;

    function getProduct()
    {
        return $this->product;
    }

    function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

}
