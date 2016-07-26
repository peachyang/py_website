<?php

namespace Seahinet\Sales\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Cart as CartSingleton;
use Seahinet\Sales\Model\Collection\Cart as Collection;

class Cart implements ListenerInterface
{

    public function afterCurrencySwitch($event)
    {
        $code = $event['code'];
        CartSingleton::instance()->convertPrice($code);
    }

    public function afterLoggedIn($event)
    {
        $customer = $event['model'];
        $collection = new Collection;
        $collection->where([
            'customer_id' => $customer->getId(),
            'status' => 1
        ])->order('id DESC');
        if ($collection->count()) {
            $cart = $collection->toArray()[0];
            $cart->combine(CartSingleton::instance());
        } else {
            CartSingleton::instance()
                    ->setData('customer_id', $customer->getId())
                    ->save();
        }
    }

    public function afterLoggedOut()
    {
        $segment = new Segment('customer');
        if ($segment->get('cart')) {
            $segment->offsetUnset('cart');
        }
    }

}
