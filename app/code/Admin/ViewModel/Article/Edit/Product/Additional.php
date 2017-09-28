<?php

namespace Seahinet\Admin\ViewModel\Catalog\Edit\Product;

class Additional extends Tab
{

    public function getAttributes()
    {
        return json_decode($this->getProduct()->offsetGet('additional'), true);
    }

}
