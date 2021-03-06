<?php

namespace Seahinet\Customer\Listeners;

use Seahinet\Customer\Model\Wishlist as Model;
use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Db\TableGateway;

class Wishlist implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\DataCache;

    public function afterAddToCart($event)
    {
        $segment = new Segment('customer');
        if ($segment->get('hasLoggedIn')) {
            $wishlist = new Model;
            $wishlist->load($segment->get('customer')->getId(), 'customer_id');
            if ($wishlist->getId()) {
                $tableGateway = new TableGateway('wishlist_item', $this->getContainer()->get('dbAdapter'));
                $tableGateway->delete([
                    'wishlist_id' => $wishlist->getId(),
                    'product_id' => $event['product_id']
                ]);
                $this->flushList('wishlist');
                $this->flushList('wishlist_item');
            }
        }
    }

}
