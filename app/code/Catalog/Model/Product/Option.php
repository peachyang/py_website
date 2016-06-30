<?php

namespace Seahinet\Catalog\Model\Product;

use Seahinet\Lib\Model\AbstractModel;

class Option extends AbstractModel
{

    protected function construct()
    {
        $this->init('product_option', 'id', ['id', 'product_id', 'input', 'is_required', 'sku', 'sort_order']);
    }

}
