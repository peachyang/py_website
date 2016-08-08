<?php

namespace Seahinet\Sales\Model\Order;

use Seahinet\Lib\Model\AbstractModel;

class Status extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_order_status', 'id', ['id', 'phase_id', 'name', 'is_default']);
    }

    public function getPhase()
    {
        if (isset($this->storage['phase_id'])) {
            return (new Phase)->load($this->storage['phase_id']);
        }
        return null;
    }

}
