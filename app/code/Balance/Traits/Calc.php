<?php

namespace Seahinet\Balance\Traits;

use Seahinet\Customer\Model\Collection\Balance as Collection;
use Seahinet\Sales\Model\Cart;
use Zend\Db\Sql\Expression;

trait Calc
{

    protected function getBalances($model)
    {
        $collection = new Collection;
        $collection->columns(['amount' => new Expression('sum(amount)')])
                ->where([
                    'customer_id' => $model->offsetGet('customer_id'),
                    'status' => 1
        ]);
        $balance = (count($collection) ? $collection[0]['amount'] : 0);
        return min($balance, $model['base_total']);
    }

}
