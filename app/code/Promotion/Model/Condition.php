<?php

namespace Seahinet\Promotion\Model;

use Seahinet\Lib\Model\AbstractModel;

class Condition extends AbstractModel
{

    protected function construct()
    {
        $this->init('promotion_condition', 'id', ['id', 'promotion_id', 'parent_id', 'identifier', 'operator', 'value']);
    }

}
