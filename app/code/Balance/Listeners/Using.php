<?php

namespace Seahinet\Balance\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Customer\Model\Balance;
use Seahinet\Customer\Model\Collection\Balance as Collection;
use Seahinet\Customer\Model\Customer;

class Using implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function afterOrderPlace($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        if ($config['balance/general/enable'] && $config['balance/general/product_for_recharge'] && $model->offsetGet('customer_id')) {
            $points = $model->getAdditional('balance');
            if ($points && $model['base_discount'] < json_decode($model['discount_detail'], true)['Promotion']) {
                $record = new Balance([
                    'customer_id' => $model->offsetGet('customer_id'),
                    'order_id' => $model->getId(),
                    'amount' => -$points,
                    'status' => 1,
                    'comment' => 'Consumption'
                ]);
                $record->save();
            }
        }
    }

}
