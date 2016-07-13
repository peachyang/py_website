<?php

namespace Seahinet\Catalog\Listeners\Price;

class Regular extends AbstractPrice
{

    public function calc($event)
    {
        $event['product']['prices']['regular'] = $this->getCurrency()->convert($event['product']['price']);
    }

}
