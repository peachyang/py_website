<?php

namespace Seahinet\Catalog\Model;

use Seahinet\Lib\Model\Eav\Entity;

class Product extends Entity
{

    const ENTITY_TYPE = 'product';

    protected function construct()
    {
        $this->init('id', ['id', 'type_id', 'attribute_set_id', 'store_id', 'sku', 'product_type_id', 'status']);
    }

}
