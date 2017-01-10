<?php

namespace Seahinet\Customer\Listeners;

use Seahinet\Customer\Model\Collection\Balance as Collection;
use Seahinet\Customer\Model\Balance as BalanceModel;
use Seahinet\Lib\Listeners\ListenerInterface;
use Zend\Db\Sql\Expression;

class Balance implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

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

    public function beforeSaveRecharge($event)
    {
        unset($event['model']['balance']);
    }

    public function afterSaveRecharge($event)
    {
        $customer = $event['model'];
        $balances = new BalanceModel;
        $balances->setData([
            'customer_id' => $customer->offsetGet('customer_id'),
            'amount' => $customer->offsetGet('balance'),
            'comment' => $customer->offsetGet('comment'),
            'status' => 0
        ]);
        $balances->save();
    }

    public function afterSaveBackendCustomer($event)
    {
        $customer = $event['model'];
        $balance = new BalanceModel;
        $balance->setData([
            'customer_id' => $customer->getId(),
            'amount' => $customer->offsetGet('adjust_balance'),
            'comment' => 'System Adjustment',
            'status' => 1
        ]);
        $balance->save();
    }

}
