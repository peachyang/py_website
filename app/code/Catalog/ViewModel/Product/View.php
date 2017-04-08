<?php

namespace Seahinet\Catalog\ViewModel\Product;

use Seahinet\Lib\ViewModel\Template;

class View extends Template
{

    protected static $product = null;

    public function getProduct()
    {
        return self::$product;
    }

    public function setProduct($product)
    {
        self::$product = $product;
        return $this;
    }

    public function getPriceBox()
    {
        $box = new Price;
        $box->setVariable('product', self::$product);
        return $box;
    }

}
