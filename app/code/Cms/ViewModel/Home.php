<?php

namespace Seahinet\Cms\ViewModel;

use Seahinet\Cms\Model\Collection\Category as Collection;
use Seahinet\Cms\Model\Collection\Page as PageCollection;
use Seahinet\Cms\Model\Category as Model;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Lib\ViewModel\Wrapper;
use Seahinet\Lib\Bootstrap;

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

    public function getChildrenCategories()
    {
        if (isset($this->storage['id'])) {
            $collection = new Collection;
            $collection->where(['parent_id' => $this->storage['id']]);
            return $collection;
        }
        return NULL;
    }

    public function getLanguageId()
    {
        return Bootstrap::getLanguage()->getId();
    }

}
