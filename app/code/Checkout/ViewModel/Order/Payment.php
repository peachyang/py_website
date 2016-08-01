<?php

namespace Seahinet\Checkout\ViewModel\Order;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Model\Cart;
use Seahinet\Sales\Source\PaymentMethod;

class Payment extends Template
{

    public function getPaymentMethods()
    {
        return (new PaymentMethod)->getSourceArray();
    }

    public function getCurrentMethod()
    {
        return Cart::instance()->offsetGet('payment_method');
    }

}
