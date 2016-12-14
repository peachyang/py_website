<?php

namespace Seahinet\Promotion\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Promotion\Model\Coupon as Model;

class Coupon implements ListenerInterface
{

    public function log($event)
    {
        $order = $event['model'];
        if (!empty($order['coupon'])) {
            $coupon = new Model;
            $coupon->load($order->offsetGet('coupon'), 'code');
            $coupon->apply($order->getId(), $order->offsetGet('customer_id') ?: null);
        }
    }

}
