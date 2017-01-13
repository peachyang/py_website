<?php

namespace Seahinet\Balance\ViewModel;

use Seahinet\Catalog\ViewModel\Product\View;
use Seahinet\Customer\ViewModel\Account;
use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Source\SourceInterface;

class Recharge extends Account
{

    protected static $product_type = null;

    public function getProduct()
    {
        $product = new Product;
        $product->product_type = 2;
    }

}
