<?php

namespace Seahinet\Catalog\ViewModel\Category;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Catalog\Model\Category;

class Toolbar extends Template
{

    /**
     * @var Category
     */
    protected $category = null;

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
        return $this;
    }

}
