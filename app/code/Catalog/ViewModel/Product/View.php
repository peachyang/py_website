<?php

namespace Seahinet\Catalog\ViewModel\Product;

use Seahinet\Lib\ViewModel\Template;

class View extends Template
{

    protected static $product = null;

    function getProduct()
    {
        return self::$product;
    }

    function setProduct($product)
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
    
    public function reviewAction()
    {
        echo 1212;die;
    }

}
