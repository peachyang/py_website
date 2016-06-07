<?php

namespace Seahinet\Lib\Model\Eav;

use Seahinet\Lib\Model\AbstractModel;

class Attribute extends AbstractModel
{

    protected function construct()
    {
        $this->init('eav_attribute', 'id', ['id', 'type_id', 'attribute_set_id', 'attribute_group_id', 'code', 'type', 'input', 'validation', 'is_required', 'default_value', 'is_unique', 'sort_order']);
    }

}
