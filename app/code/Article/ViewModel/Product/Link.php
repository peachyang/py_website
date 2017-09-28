<?php

namespace Seahinet\Article\ViewModel\Product;

use Seahinet\Article\ViewModel\Category\ProductList;

abstract class Link extends ProductList
{

    use \Seahinet\Lib\Traits\DB;

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
