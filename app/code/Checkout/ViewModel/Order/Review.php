<?php

namespace Seahinet\Checkout\ViewModel\Order;

use Seahinet\Checkout\ViewModel\Cart;
use Seahinet\Checkout\ViewModel\Cart\Item;

class Review extends Cart
{

    public function getRow($item)
    {
        $row = new Item;
        $row->setTemplate('checkout/review/item');
        $row->setVariable('item', $item);
        return $row;
    }

}
