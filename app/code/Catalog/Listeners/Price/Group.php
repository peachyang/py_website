<?php

namespace Seahinet\Catalog\Listeners\Price;

use Seahinet\Lib\Session\Segment;

class Group extends AbstractPrice
{

    public function calc($event)
    {
        $product = $event['product'];
        $segment = new Segment('customer');
        if ($product['group_price'] && ($price = json_decode($product['group_price'], true))) {
            $prices = [];
            foreach ($segment->get('hasLoggedIn') ? $segment->get('customer')->getGroup() : [['id' => 0]] as $group) {
                if (isset($price[$group['id']])) {
                    $prices[] = $price[$group['id']];
                }
            }
            if ($prices) {
                $product['base_prices']['group'] = min($prices);
                $product['prices']['group'] = $this->getCurrency()->convert($product['base_prices']['group']);
            }
        }
    }

}
