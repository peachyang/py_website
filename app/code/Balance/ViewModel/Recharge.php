<?php

namespace Seahinet\Balance\ViewModel;

use Seahinet\Lib\Session\Segment;
use Seahinet\Catalog\ViewModel\Product\View;
use Seahinet\Customer\ViewModel\Account;

//use Seahinet\Customer\Model\Collection\Balance;

class Recharge extends Account
{

    public function getProduct()
    {
        $product = new View;
        $rechagePro = $product->getProduct();
    }

    public function getPriceBox()
    {
        $product = new View;
        $price = $product->getPriceBox();
    }

}
