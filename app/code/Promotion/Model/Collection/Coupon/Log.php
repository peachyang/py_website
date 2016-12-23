<?php

namespace Seahinet\Promotion\Model\Collection\Coupon;

use Seahinet\Lib\Model\AbstractCollection;

class Log extends AbstractCollection
{
    protected function construct()
    {
        $this->init('promotion_coupon_log');
    }
}
