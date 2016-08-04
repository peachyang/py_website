<?php

namespace Seahinet\Cms\ViewModel;

use Seahinet\Cms\Model\Category;
use Seahinet\Lib\ViewModel\Template;

class Navigation extends Template
{

    protected $navigations = null;
    protected $urls = [];

    public function getTemplate()
    {
        if (!$this->template) {
            return 'cms/main' . $this->getQuery();
        }
        return parent::getTemplate();
    }

    public function getCategory()
    {
        return $this->getVariable('category');
    }

    public function getNavigation()
    {
        return $this->navigations;
    }

    public function setNavigation($navigations)
    {
        $this->navigations = $navigations;
        return $this;
    }

}
