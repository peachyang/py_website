<?php

namespace Seahinet\Article\ViewModel;

use Seahinet\Article\Model\Collection\Category;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\ViewModel\Template;

class Navigation extends Template
{

    public function getRootCategory()
    {
        $categories = new Category;
        $categories->where(['parent_id' => null]);
        if (count($categories)) {
            return $categories[0];
        }
        return [];
    }

}
