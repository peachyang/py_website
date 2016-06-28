<?php

namespace Seahinet\Catalog\Model;

use Seahinet\Lib\Model\Eav\Entity;

class Category extends Entity
{

    const ENTITY_TYPE = 'category';

    protected function construct()
    {
        $this->init('id', ['id', 'type_id', 'attribute_set_id', 'store_id', 'parent_id', 'sort_order', 'status']);
    }

}
