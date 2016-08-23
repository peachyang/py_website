<?php

namespace Seahinet\Promotion\Model;

use Seahinet\Lib\Model\AbstractModel;

class Rule extends AbstractModel
{

    protected function construct()
    {
        $this->init('promotion', 'id', ['id', 'name', 'description', 'store_id', 'status', 'use_coupon', 'from_date', 'to_date', 'stop_processing', 'sort_order']);
    }

}
