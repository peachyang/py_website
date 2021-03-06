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
        $price = (float) $event['product']['special_price'];
        if ($price && (!$start || $now >= $start) && (!$end || $now <= $end)) {
            $product['base_prices']['special'] = $price;
            $product['prices']['special'] = $this->getCurrency()->convert($price);
        }
    }

}
