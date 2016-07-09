<?php

namespace Seahinet\Catalog\Model\Collection\Product;

use Seahinet\Lib\Model\AbstractCollection;

class Rating extends AbstractCollection
{
    protected function construct()
    {
        $this->init('rating');
    }
}
