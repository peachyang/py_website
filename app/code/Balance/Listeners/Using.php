<?php

namespace Seahinet\Balance\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Customer\Model\Balance;
use Seahinet\Customer\Model\Collection\Balance;
use Seahinet\Customer\Model\Customer;

class Using implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function calc($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        if ($config['balance/general/enable'] && $config['balance/general/product_for_recharge'] && $model->offsetGet('customer_id')) {
            $additional = $model['additional'] ? json_decode($model['additional'], true) : [];
            if (!empty($additional['balance'])) {
                $balances = (new Customer)->getBalance($model, true);
                $additional['balance'] = min($additional['balance'], $balances);
                $discount = $additional['balance'];
                $model->setData([
                    'additional' => json_encode($additional),
                    'base_discount' => (float) $model->offsetGet('base_discount') - $discount,
                    'discount_detail' => json_encode([$config['rewardpoints/general/title'] => - $discount] + (json_decode($model['discount_detail'], true) ?: []))
                ])->setData('discount', $model->getCurrency()->convert($model->offsetGet('base_discount')));
            }
        }
    }

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
