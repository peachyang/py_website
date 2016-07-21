<?php

namespace Seahinet\Catalog\ViewModel\Product;

use Seahinet\Catalog\ViewModel\Category\ProductList;

abstract class Link extends ProductList
{

    protected $limit = null;

    public function getLimit()
    {
        return $this->limit;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

}
