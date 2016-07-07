<?php

namespace Seahinet\Catalog\Controller;

use Seahinet\Catalog\Model\Category;
use Seahinet\Lib\Controller\ActionController;

class CategoryController extends ActionController
{

    public function indexAction()
    {
        if ($this->getOption('category_id')) {
            $category = new Category;
            $category->load($this->getOption('category_id'));
            
        }
        return $this->notFoundAction();
    }

}
