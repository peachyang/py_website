<?php

namespace Seahinet\Balance\Traits;

use Seahinet\Customer\Model\Collection\Balance as Collection;
use Zend\Db\Sql\Expression;

trait Calc
{

    protected function getBalances($model, $withUsed = false)
    {
        $collection = new Collection;
        $collection->columns(['amount' => new Expression('sum(amount)')])
                ->where([
                    'customer_id' => $model->offsetGet('customer_id'),
                    'status' => 1
        ]);
        $balance = (count($collection) ? $collection[0]['amount'] : 0);
        $additional = $model['additional'] ? json_decode($model['additional'], true) : [];
        return min($balance, $model['base_total'] + @$additional['balance'] ?? 0);
    }

}
