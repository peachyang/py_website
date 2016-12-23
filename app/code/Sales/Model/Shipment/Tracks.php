<?php

namespace Seahinet\Sales\Model\Shipment;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Sales\Model\Collection\Shipment\Track;

class Tracks extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_order_shipment_track', 'id', [
            'id', 'shipment_id', 'order_id', 'carrier',
            'carrier_code', 'track_number', 'description'
        ]);
    }

}
