<?php

namespace Seahinet\Checkout\ViewModel\Order;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Model\Cart;
use Seahinet\Sales\Source\ShippingMethod;

class Shipping extends Template
{

    public function getShippingMethods($storeId)
    {
        if (Cart::instance()->isVirtual($storeId)) {
            return [];
        }
        return (new ShippingMethod)->getSourceArray($storeId);
    }

    public function getCurrentMethod()
    {
        if ($method = json_decode(Cart::instance()->offsetGet('shipping_method'), true)) {
            return $method;
        }
        return [];
    }

}
