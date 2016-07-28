<?php

namespace Seahinet\Checkout\ViewModel\Cart;

use Seahinet\Lib\ViewModel\Template;

class Item extends Template
{

    protected static $currency = null;

    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

}
