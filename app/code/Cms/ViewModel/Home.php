<?php

namespace Seahinet\Cms\ViewModel;

use Seahinet\Cms\Model\Collection\Category as Collection;
use Seahinet\Cms\Model\Category as Model;
use Seahinet\Lib\ViewModel\Template;

class Home extends Template
{

    protected $category = null;

    public function getCategoryPage($category_id)
    {
        if (is_null($category_id)) {
            return null;
        }
        $category = new Model;
        $category->load($category_id);
        $pages = $category->getPages();
        $query = $this->getRequest()->getQuery();
        $pages->limit(10);
        return $pages;
    }

    public function getCategory()
    {
        return $this->getVariable('category');
    }

    public function getParentCategory()
    {
        if (!empty($this->storage['parent_id'])) {
            $navgiation = new static;
            $navgiation->load($this->storage['parent_id']);
            return $navgiation;
        }
        return NULL;
    }

//    public function getChildrenCategories()
//    {
//        if (isset($this->storage['id'])) {
//            $collection = new Collection;
//            $collection->where(['parent_id' => $this->storage['id']]);
//            return $collection;
//        }
//    }

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
