<?php

namespace Seahinet\Sales\Model;

use Seahinet\Lib\Model\AbstractCollection;

class Shipment extends AbstractCollection
{

    protected function construct()
    {
        $this->init('sales_order_shipment');
    }

}
