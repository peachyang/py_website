<?php

namespace Seahinet\Checkout\ViewModel\Order;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Model\Cart;
use Seahinet\Sales\Source\ShippingMethod;

class Shipping extends Template
{

    public function getShippingMethods()
    {
        return (new ShippingMethod)->getSourceArray();
    }

    public function getCurrentMethod()
    {
        if ($method = json_decode(Cart::instance()->offsetGet('payment_method'))) {
            return $method;
        }
        return [];
    }

}
