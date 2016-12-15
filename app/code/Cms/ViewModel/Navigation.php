<?php

namespace Seahinet\Cms\ViewModel;

use Seahinet\Cms\Model\Category;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\ViewModel\Template;

class Navigation extends Template
{

    protected $navigations = null;
    protected $urls = [];

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

    public function getLanguageId()
    {
        return Bootstrap::getLanguage()->getId();
    }

}
