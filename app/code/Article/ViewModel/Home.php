<?php

namespace Seahinet\Article\ViewModel;

use Seahinet\Article\Model\Collection\Category as Collection;
use Seahinet\Article\Model\Category;
use Seahinet\Lib\ViewModel\Template;

class Home extends Template
{

    public function getCategory()
    {
        return $this->getVariable('category', null);
    }

    public function setCategory(Category $category)
    {
        $this->variables['category'] = $category;
        return $this;
    }

    public function getCategoryProduct($category_id)
    {
        if (is_null($category_id)) {
            return null;
        }
        $category = new Category;
        $category->load($category_id);
        $products = $category->getProducts();
        return $products;
    }

    public function getRootCategory()
    {
        $categories = new Collection;
        $categories->where(['parent_id' => null]);
        if (count($categories)) {
            return $categories[0];
        }
        return [];
    }

    public function getChildrenCategories($category_id)
    {
        if (is_null($category_id)) {
            return null;
        }
        $category = new Category;
        $category->load($category_id);
        $categories = $category->getChildrenCategories();
        return $categories;
    }

}
