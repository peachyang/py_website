<?php

namespace Seahinet\Cms\ViewModel;

use Seahinet\Cms\Model\Collection\Category as Collection;
use Seahinet\Cms\Model\Collection\Page as PageCollection;
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

    public function getTopCategory($category = null)
    {
        if (is_null($category)) {
            $category = $this->getCategory();
        }
        $parent = $category->getParentCategory();
        return $parent ? $this->getTopCategory($parent) : $category;
    }
    
}
