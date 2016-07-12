<?php

namespace Seahinet\Catalog\Model\Product;

use Seahinet\Lib\Model\AbstractModel;

class Type extends AbstractModel
{

    protected function construct()
    {
        $this->init('product_type', 'id', ['id', 'code', 'name']);
    }

}
