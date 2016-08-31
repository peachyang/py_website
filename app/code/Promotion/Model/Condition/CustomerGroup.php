<?php

namespace Seahinet\Promotion\Model\Condition;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate\Operator;

class CustomerGroup implements ConditionInterface
{

    public function match($model, $condition, $storeId)
    {
        if ($condition['identifier'] === 'customer_group') {
            $tableGateway = new TableGateway('customer_in_group', $this->getContainer()->get('dbAdapter'));
            $select = $tableGateway->getSql()->select();
            $select->where(new Operator('group_id', preg_replace('/[^\<\>\=\!]/', '', $condition['operator']), $condition['value']))
                    ->where(['customer_id' => $model->offsetGet('customer_id')]);
            return (bool) count($tableGateway->selectWith($select)->toArray());
        }
        return false;
    }

}