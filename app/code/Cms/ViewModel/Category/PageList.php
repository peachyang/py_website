<?php

namespace Seahinet\Cms\ViewModel\Category;

use Seahinet\Cms\Model\Category;
use Seahinet\Lib\ViewModel\Template;

class PageList extends Template
{

    protected $pages = null;
    protected $urls = [];
    protected $indexer = null;

    public function getTemplate()
    {
        if (!$this->template) {
            return 'cms/page/list/' . $this->getQuery('mode', 'grid');
        }
        return parent::getTemplate();
    }

    public function getCategory()
    {
        return $this->getVariable('category', null);
    }

    public function setCategory(Category $category)
    {
        $this->variables['category'] = $category;
        return $this;
    }

    public function getPages()
    {
        return $this->pages;
    }

    public function setPages($pages)
    {
        $this->pages = $pages;
        return $this;
    }

}
