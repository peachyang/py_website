<?php

namespace Seahinet\Admin\ViewModel\Promotion\Edit;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Promotion\Model\Collection\Coupon as Collection;

class Coupon extends Template
{
    
    public function getCoupons()
    {
        if ($id = $this->getQuery('id')) {
            $collection = new Collection;
            $collection->where(['promotion_id' => $id])
                    ->order('status DESC');
            return $collection;
        }
        return [];
    }
}
