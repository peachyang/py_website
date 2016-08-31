<?php

namespace Seahinet\Checkout\ViewModel\Order;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Promotion\Model\Collection\Rule;
use Seahinet\Sales\Model\Cart;

class Coupon extends Template
{

    public function getCurrent()
    {
        return $this->getVariable('store') ? json_decode(Cart::instance()->offsetGet('coupon'), true)[$this->getVariable('store')] : '';
    }

    public function getCoupons()
    {
        $time = time();
        $rules = new Rule;
        $rules->where([ 'use_coupon' => 1, 'status' => 1])
                ->order('sort_order');
        $result = [];
        $storeId = $this->getVariable('store');
        foreach ($rules as $rule) {
            if ((empty($rule->offsetGet('store_id')) || in_array($storeId, (array) $rule->offsetGet('store_id'))) &&
                    (empty($rule->offsetGet('from_date')) || $time >= strtotime($rule->offsetGet('from_date'))) &&
                    (empty($rule->offsetGet('to_date')) || $time <= strtotime($rule->offsetGet('to_date'))) &&
                    $rule->getCondition()->match(Cart::instance(), $storeId)) {
                foreach ($rule->getCoupon() as $coupon) {
                    if ($rule->matchCoupon($coupon['code'], Cart::instance())) {
                        $result[] = [
                            'code' => $coupon->offsetGet('code'),
                            'title' => $rule->offsetGet('name')
                        ];
                        break;
                    }
                }
            }
        }
        return $result;
    }

}
