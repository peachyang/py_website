<?php

namespace Seahinet\Sales\Model\Order;

use Seahinet\Lib\Model\AbstractModel;

class Status extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_order_status', 'id', ['id', 'phase_id', 'name', 'is_default']);
    }
    
}
