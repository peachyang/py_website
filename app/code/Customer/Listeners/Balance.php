<?php

namespace Seahinet\Customer\Listeners;

use Seahinet\Customer\Model\Collection\Balance as Collection;
use Seahinet\Lib\Listeners\ListenerInterface;
use Zend\Db\Sql\Expression;

class Balance implements ListenerInterface
{

    public function calc($event)
    {
        $balance = new Collection;
        $balance->columns(['balance' => new Expression('sum(amount)')])
                ->group('customer_id')
                ->where(['customer_id' => $event['customer']->getId()]);
        if ($balance->count()) {
            $event['customer']->setData('balance', $balance[0]['balance']);
        }
    }

}
