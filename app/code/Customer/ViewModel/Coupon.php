<?php

namespace Seahinet\Customer\ViewModel;

use Seahinet\Customer\ViewModel\Account;
use Seahinet\Promotion\Model\Collection\Rule;

class Coupon extends Account
{

    public function getCoupons()
    {
        $collection = new Rule;
        $collection->where(['use_coupon' => 1]);
        $result = [];
        foreach ($collection as $rule) {
            if ($this->match($rule->getCondition())) {
                $result[] = $rule;
            }
        }
        return $result;
    }

    public function match($condition, $default = true)
    {
        if ($condition['identifier'] === 'customer_id') {
            return $condition['operator'] === '=' ? $this->getCustomer()->getId() == $condition['value'] : $this->getCustomer()->getId() != $condition['value'];
        } else if ($condition['identifier'] === 'customer_group') {
            foreach ($this->getCustomer()->getGroup() as $group) {
                if ($condition['operator'] === '=' && $group['id'] == $condition['value'] || $condition['operator'] !== '=' && $group['id'] != $condition['value']) {
                    return true;
                }
            }
            return false;
        } else if ($condition['identifier'] === 'customer_level') {
            return $condition['operator'] === '=' ? $this->getCustomer()->getLevel() == $condition['value'] : $this->getCustomer()->getLevel() != $condition['value'];
        } else if ($condition['identifier'] === 'combination') {
            $result = $condition['operator'] === 'and' ? 1 : 0;
            foreach ($condition->getChildren() as $child) {
                if ($condition['operator'] === 'and') {
                    $result &= (int) $this->match($child, $condition['value']);
                    if (!$result) {
                        break;
                    }
                } else {
                    $result |= (int) $this->match($child, $condition['value']);
                    if ($result) {
                        break;
                    }
                }
            }
            return $result === (int) $condition['value'];
        } else {
            return $default;
        }
    }

}
