<?php

namespace Seahinet\Retailer\ViewModel\Catalog\Edit\Product;

class Store extends Tab
{

    protected $activeIds = null;

    public function getActiveIds()
    {
        if (is_null($this->activeIds)) {
            $this->activeIds = $this->getProduct()['store_category'];
        }
        return $this->activeIds;
    }

}
