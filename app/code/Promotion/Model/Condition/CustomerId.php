<?php

namespace Seahinet\Promotion\Model\Condition;

use Seahinet\Customer\Model\Customer;

class CustomerId implements ConditionInterface
{

    public function match($model, $condition, $storeId)
    {
        if ($condition['identifier'] === 'customer_id') {
            $value = new Customer;
            $value->load($model['customer_id']);
            if ($value = (int) $value->getId()) {
                switch ($condition['operator']) {
                    case '=':
                        return $value === (int) $condition['value'];
                    case '<>':
                    case '!=':
                        return $value !== (int) $condition['value'];
                }
            }
        }
        return false;
    }

}
