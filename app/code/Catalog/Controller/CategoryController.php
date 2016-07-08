<?php

namespace Seahinet\Catalog\Controller;

use Seahinet\Catalog\Model\Category;
use Seahinet\Lib\Controller\ActionController;

class CategoryController extends ActionController
{
    
    use \Seahinet\Catalog\Traits\Breadcrumb;

    public function indexAction()
    {
        if ($this->getOption('category_id')) {
            $category = new Category;
            $category->load($this->getOption('category_id'));
            $root = $this->getLayout('catalog_category');
            $root->getChild('head')->setTitle($category['meta_title']? : $category['name'])
                    ->setDescription($category['meta_description'])
                    ->setKeywords($category['meta_keywords']);
            $content = $root->getChild('content');
            $this->generateCrumbs($content->getChild('breadcrumb'), $this->getOption('category_id'));
            $content->getChild('toolbar')->setCategory($category);
            $content->getChild('list')->setProducts($category->getProducts());
            return $root;
        }
        return $this->notFoundAction();
    }

}
