<?php

namespace Seahinet\Checkout\ViewModel\Order;

use Seahinet\Checkout\ViewModel\Cart;

class Review extends Cart
{

    public function getRow($item, $rowspan = 0)
    {
        $row = $this->getChild('item');
        $row->setVariable('item', $item)
                ->setVariable('rowspan', $rowspan);
        return $row;
    }

}
