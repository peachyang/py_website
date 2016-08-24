<?php

namespace Seahinet\Admin\ViewModel\Promotion\Edit;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Promotion\Model\Collection\Coupon as Collection;

class Coupon extends Template
{

    public function getCoupons()
    {
        $collection = new Collection;
        return $collection;
    }

}
