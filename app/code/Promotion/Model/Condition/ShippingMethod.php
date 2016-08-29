<?php

namespace Seahinet\Promotion\Model\Condition;

class ShippingMethod implements ConditionInterface
{

    public function match($model, $condition, $storeId)
    {
        if ($condition['identifier'] === 'shipping_method') {
            $value = $model->offsetGet('shipping_method');
            if (strpos($value, ':')) {
                $value = json_decode($value, true);
                if ($condition['store_id']) {
                    $value = $value[$condition['store_id']];
                } else {
                    foreach ($value as $method) {
                        switch ($condition['operator']) {
                            case '=':
                                if ($method === $condition['value']) {
                                    return true;
                                }
                                break;
                            case '<>':
                            case '!=':
                                if ($method !== $condition['value']) {
                                    return true;
                                }
                                break;
                        }
                    }
                    return false;
                }
            }
            switch ($condition['operator']) {
                case '=':
                    return $value === $condition['value'];
                case '<>':
                case '!=':
                    return $value !== $condition['value'];
            }
        }
        return false;
    }

}
