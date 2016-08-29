<?php

namespace Seahinet\Promotion\Model\Condition;

class Subtotal implements ConditionInterface
{

    public function match($model, $condition, $storeId)
    {
        if ($condition['identifier'] === 'subtotal') {
            switch ($condition['operator']) {
                case '=':
                    return (float) $model['base_subtotal'] === (float) $condition['value'];
                case '<>':
                case '!=':
                    return (float) $model['base_subtotal'] !== (float) $condition['value'];
                case '>':
                    return (float) $model['base_subtotal'] > (float) $condition['value'];
                case '>=':
                    return (float) $model['base_subtotal'] >= (float) $condition['value'];
                case '<':
                    return (float) $model['base_subtotal'] < (float) $condition['value'];
                case '<=':
                    return (float) $model['base_subtotal'] <= (float) $condition['value'];
                case 'in':
                    return in_array((float) $model['base_subtotal'], explode(',', $condition['value']));
                case 'not in':
                case 'nin':
                    return !in_array((float) $model['base_subtotal'], explode(',', $condition['value']));
            }
        }
        return false;
    }

}
