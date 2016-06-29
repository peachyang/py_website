<?php

namespace Seahinet\Admin\ViewModel\Catalog\Edit\Product;

use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\ViewModel\AbstractViewModel;

class Tab extends AbstractViewModel
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
