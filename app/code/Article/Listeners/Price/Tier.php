<?php

namespace Seahinet\Catalog\Listeners\Price;

use Seahinet\Lib\Session\Segment;

class Tier extends AbstractPrice
{

    public function calc($event)
    {
        $product = $event['product'];
        $segment = new Segment('customer');
        if ($product['tier_price'] && $event['qty'] &&
                ($tier = json_decode($product['tier_price'], true))) {
            $prices = [];
            foreach ($segment->get('hasLoggedIn') ? $segment->get('customer')->getGroup() : [['id' => 0]] as $group) {
                if (isset($tier[$group['id']])) {
                    $tmp = $tier[$group['id']];
                    ksort($tmp, SORT_NATURAL);
                    foreach ($tmp as $key => $price) {
                        if ($event['qty'] >= $key) {
                            $prices[] = $price;
                            break;
                        }
                    }
                }
            }
            if (isset($tier[-1])) {
                $tmp = $tier[-1];
                ksort($tmp, SORT_NATURAL);
                foreach ($tmp as $key => $price) {
                    if ($event['qty'] >= $key) {
                        $prices[] = $price;
                        break;
                    }
                }
            }
            if ($prices) {
                $product['base_prices']['tier'] = min($prices);
                $product['prices']['tier'] = $this->getCurrency()->convert($product['base_prices']['tier']);
            }
        }
    }

}
