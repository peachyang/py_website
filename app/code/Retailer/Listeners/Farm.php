<?php

namespace Seahinet\Retailer\Listeners;

use Exception;
use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Lib\Session\Segment;
use Seahinet\Retailer\Model\Retailer;
use Seahinet\Sales\Model\Cart;

class Farm implements ListenerInterface
{

    public function check($event)
    {
        $segment = new Segment('customer');
        if ($segment->get('hasLoggedIn')) {
            $retailer = new Retailer;
            $retailer->load($segment->get('customer')->getId(), 'customer_id');
            if (isset($event['product'])) {
                $product = $event['product'];
            } else if (isset($event['product_id'])) {
                $product = new Product;
                $product->load($event['product_id']);
            }
            if (isset($product) && $retailer->getId() && $retailer->offsetGet('store_id') == $product->offsetGet('store_id')) {
                throw new Exception('Click farming check failed. Retailer ID:' . $retailer->getId());
            }
        }
    }

    public function beforeCombine($event)
    {
        $customer = $event['model'];
        $retailer = new Retailer;
        $retailer->load($customer->getId(), 'customer_id');
        $cart = Cart::instance();
        foreach ($cart->getItems() as $item) {
            if ($retailer['store_id'] == $item['store_id']) {
                $cart->removeItem($item);
            }
        }
    }

}
