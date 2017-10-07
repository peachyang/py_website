<?php

namespace Seahinet\Admin\ViewModel\Catalog\Edit\Product;

use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\ViewModel\Template;

class Tab extends Template
{

    private static $product;

    public function getProduct()
    {
        self::$product = new Product;
        if ($this->getQuery('id')) {
            self::$product->load($this->getQuery('id'));
        }
        return self::$product;
    }

}
