<?php

namespace Seahinet\Retailer\ViewModel\Catalog\Edit\Product;

class Additional extends Tab
{

    public function getAttributes()
    {
        return json_decode($this->getProduct()->offsetGet('additional'), true);
    }

}
