<?php

namespace Seahinet\Catalog\Controller;

use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Controller\ActionController;

class ProductController extends ActionController
{

    public function indexAction()
    {
        if ($this->getOption('product_id')) {
            $product = new Product;
            $product->load($this->getOption('product_id'));
            
        }
        return $this->notFoundAction();
    }

}
