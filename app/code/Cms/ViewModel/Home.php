<?php

namespace Seahinet\Cms\ViewModel;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Article\Model\Collection\Category as Collection;
use Seahinet\Cms\Model\Collection\Category as CmsCollection;

class Home extends Template
{

    public function getTopCategory()
    {
        $categories = new CmsCollection;
        $categories->where(['parent_id' => null]);
        if (count($categories)) {
            return $categories[0];
        }
        return [];
    }

    public function getLanguageId()
    {
        return Bootstrap::getLanguage()->getId();
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

}
