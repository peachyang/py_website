<?php

namespace Seahinet\Retailer\Listeners;

use Exception;
use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Lib\Session\Segment;
use Seahinet\Retailer\Model\Retailer;

class Farm implements ListenerInterface
{

    public function check($event)
    {
        $segment = new Segment('customer');
        if ($segment->get('hasLoggedIn')) {
            $retailer = new Retailer;
            $retailer->load($segment->get('customer')->getId(), 'customer_id');
            if ($retailer->getId() && $retailer->offsetGet('store_id') == $event['product']->offsetGet('store_id')) {
                throw new Exception('Click farming check failed. Retailer ID:' . $retailer->getId());
            }
        }
    }

}
