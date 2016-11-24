<?php

namespace Seahinet\Cms\ViewModel;

use Seahinet\Cms\Model\Category as Model;
use Seahinet\Lib\ViewModel\Template;

class Category extends Template
{

    protected $category = null;

    public function __construct()
    {
        $this->setTemplate('cms/category');
    }

    public function getCategory()
    {
        if (is_null($this->category)) {
            $this->category = new Model;
            $this->category->load($this->getVariable('id'));
        }
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    public function getPages()
    {
        if ($pages = $this->getCategory()->getPages()) {
            $pages->order('created_at DESC');
            if ($this->getVariable('count')) {
                $pages->limit((int)$this->getVariable('count'));
            }
        }
        return $pages ?: [];
    }

}
