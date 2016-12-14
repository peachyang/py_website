<?php

namespace Seahinet\Retailer\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class ProductInCategoryCollection extends AbstractCollection
{

    protected function construct()
    {
        $this->init('product_in_category', 'id', ['id', 'product_id', 'category_id', 'sort_order']);
    }

}
