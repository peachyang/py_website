<?php

namespace Seahinet\Sales\Model\Collection\Cart;

use Seahinet\Lib\Model\AbstractCollection;

class Item extends AbstractCollection
{

    protected function construct()
    {
        $this->init('sales_cart_item');
    }

}
