<?php

namespace Seahinet\Sales\Model\Collection\Shipment;

use Seahinet\Lib\Model\AbstractCollection;

class Track extends AbstractCollection
{

    protected function construct()
    {
        $this->init('sales_order_shipment_track');
    }

}
