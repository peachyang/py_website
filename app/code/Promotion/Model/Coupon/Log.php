<?php

namespace Seahinet\Promotion\Model\Coupon;

use Seahinet\Lib\Model\AbstractModel;

class Log extends AbstractModel
{

    protected function construct()
    {
        $this->init('promotion_coupon_log', 'id', ['id', 'coupon_id', 'order_id', 'customer_id']);
    }

}
