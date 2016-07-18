<?php

namespace Seahinet\Sales\Model\Collection\Order;

use Seahinet\Lib\Model\AbstractCollection;

class Status extends AbstractCollection
{

    protected function construct()
    {
        $this->init('sales_order_status');
    }

}
