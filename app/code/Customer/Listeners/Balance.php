<?php

namespace Seahinet\Customer\Listeners;

use Seahinet\Customer\Model\Customer;
use Seahinet\Customer\Model\Collection\Balance as Collection;
use Seahinet\Customer\Model\Balance as BalanceModel;
use Seahinet\Lib\Listeners\ListenerInterface;
use Zend\Db\Sql\Expression;

class Balance implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;
    use \Seahinet\Lib\Traits\DB;

    public function calc($event)
    {
        $balance = new Collection;
        $balance->columns(['balance' => new Expression('sum(amount)')])
                ->group('customer_id')
                ->where(['customer_id' => $event['customer']->getId(), 'status' => 1]);
        $balance->load(FALSE, TRUE);
        if ($balance->count()) {
            $event['customer']->setData('balance', $balance[0]['balance']);
        }
    }

    public function beforeSaveRecharge($event)
    {
        unset($event['data']['balance']);
    }

    public function afterSaveRecharge($event)
    {
        $data = $event['data'];
        $recharge = new BalanceModel;
        $recharge->load($data['qty']);
        $recharge->setData([
            'customer_id' => $data['customer_id'],
            'product_id' => $data['product_id'],
            'amount' => $data['qty'],
            'status' => 0
        ])->save();
    }

    public function afterSaveBackendCustomer($event)
    {
        $customer = $event['model'];
        if ($amount = (float) $customer->offsetGet('adjust_balance')) {
            $balance = new BalanceModel;
            $balance->setData([
                'customer_id' => $customer->getId(),
                'amount' => $amount,
                'comment' => 'System Adjustment',
                'status' => 1
            ]);
            $balance->save();
        }
    }

}
