<?php

namespace Seahinet\Promotion\Model;

use Seahinet\Lib\Model\AbstractModel;

class Coupon extends AbstractModel
{

    protected function construct()
    {
        $this->init('promotion_coupon', 'id', ['id', 'code', 'customer_id', 'store_id', 'promotion_id', 'uses_per_coupon', 'uses_per_customer']);
    }

}
