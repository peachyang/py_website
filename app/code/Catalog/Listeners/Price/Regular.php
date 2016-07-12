<?php

namespace Seahinet\Catalog\Listeners\Price;

class Regular implements PriceInterface
{

    public function calc($event)
    {
        $event['product']['prices'][] = (float) $event['product']['price'];
    }

}
