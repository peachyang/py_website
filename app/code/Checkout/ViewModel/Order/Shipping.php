<?php

namespace Seahinet\Checkout\ViewModel\Order;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Source\ShippingMethod;

class Shipping extends Template
{

    public function getShippingMethods()
    {
        return (new ShippingMethod)->getSourceArray();
    }

}
