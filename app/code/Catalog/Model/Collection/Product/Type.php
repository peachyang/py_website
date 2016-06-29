<?php

namespace Seahinet\Catalog\Model\Collection\Product;

use Seahinet\Lib\Model\AbstractCollection;

class Type extends AbstractCollection
{

    protected function construct()
    {
        $this->init('product_type');
    }

}
