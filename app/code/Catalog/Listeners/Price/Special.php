<?php

namespace Seahinet\Catalog\Listeners\Price;

class Special implements PriceInterface
{

    public function calc($event)
    {
        $product = $event['product'];
        $now = time();
        if ($now >= strtotime($product['special_price_start']) && $now <= strtotime($product['special_price_end'])) {
            $product['prices'][] = (float) $event['product']['special_price'];
        }
    }

}
