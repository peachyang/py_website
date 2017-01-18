<?php

namespace Seahinet\Balance\Listerers;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Customer\Model\Balance;

class Recalc implements ListenerInterface
{

    use \Seahinet\RewardPoints\Traits\Recalc;

    public function afterCustomerLogin($event)
    {
        $customer = $event['model'];
        $this->recalc($customer->getId());
    }

    public function afterOrderRecharge($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        if ($config['balance/general/enable'] && $config['balance/general/product_for_recharge'] && $model->offsetGet('customer_id')) {
            $recharge = new Balance([
                'customer_id' => $model->offsetGet('customer_id'),
                'order_id' => $model->getId(),
                'amount' => $model->offsetGet('total'),
                'comment' => '',
                'status' => 0
            ]);
            $recharge->save();
        }
    }

}
