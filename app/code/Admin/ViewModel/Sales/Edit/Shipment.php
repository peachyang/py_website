<?php

namespace Seahinet\Admin\ViewModel\Sales\Edit;

use Seahinet\Admin\ViewModel\Sales\View\Order;
use Seahinet\Shipping\Source\Carrier;

class Shipment extends Order
{

    public function getCarriers()
    {
        return (new Carrier)->getSourceArray();
    }

}
