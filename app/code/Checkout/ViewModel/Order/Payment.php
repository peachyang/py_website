<?php

namespace Seahinet\Checkout\ViewModel\Order;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Source\PaymentMethod;

class Payment extends Template
{

    public function getPaymentMethods()
    {
        return (new PaymentMethod)->getSourceArray();
    }

}
