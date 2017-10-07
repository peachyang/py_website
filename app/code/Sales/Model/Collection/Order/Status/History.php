<?php

namespace Seahinet\Sales\Model\Collection\Order\Status;

use Seahinet\Lib\Model\AbstractCollection;

class History extends AbstractCollection
{

    protected function construct()
    {
        $this->init('sales_order_status_history');
    }

}
