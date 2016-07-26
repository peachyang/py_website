<?php

namespace Seahinet\Catalog\Controller;

use Seahinet\Catalog\Model\Category;
use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Controller\ActionController;

class ProductController extends ActionController
{

    use \Seahinet\Catalog\Traits\Breadcrumb;

    public function indexAction()
    {
        if ($this->getOption('product_id')) {
            $product = new Product;
            $product->load($this->getOption('product_id'));
            if ($product->getId()) {
                $root = $this->getLayout('catalog_product');
                $root->getChild('head')->setTitle($product->offsetGet('meta_title')? : $product->offsetGet('name'))
                        ->setDescription($product->offsetGet('meta_description'))
                        ->setKeywords($product->offsetGet('meta_keywords'));
                $root->getChild('product', true)->setProduct($product);
                $breadcrumb = $root->getChild('breadcrumb', true);
                $this->generateCrumbs($breadcrumb, $this->getOption('category_id'));
                $breadcrumb->addCrumb([
                    'label' => $product->offsetGet('name')
                ]);
                return $root;
            }
        }
        return $this->notFoundAction();
    }

}
