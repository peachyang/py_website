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
            if ($product->getId()) {
                $root = $this->getLayout('catalog_product');
                $root->getChild('product', true)->setProduct($product);
                return $root;
            }
        }
        return $this->notFoundAction();
    }

}
