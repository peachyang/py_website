<?php

namespace Seahinet\Promotion\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Coupon extends AbstractCollection
{

    protected function construct()
    {
        $this->init('promotion_coupon');
    }

}
