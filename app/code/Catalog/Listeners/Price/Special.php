<?php

namespace Seahinet\Catalog\Listeners\Price;

class Special extends AbstractPrice
{

    public function calc($event)
    {
        $product = $event['product'];
        $now = time();
        $start = strtotime($product['special_price_start']);
        $end = strtotime($product['special_price_end']);
        if ((!$start || $now >= $start) && (!$end || $now <= $end)) {
            $product['prices']['special'] = $this->getCurrency()->convert($event['product']['special_price']);
        }
    }

}
