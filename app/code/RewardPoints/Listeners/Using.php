<?php

namespace Seahinet\RewardPoints\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\RewardPoints\Model\Collection\Record as Collection;
use Seahinet\RewardPoints\Model\Record;

class Using implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\RewardPoints\Traits\Calc;

    public function apply($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        $count = $event['count'] ?: false;
        if ($config['rewardpoints/general/enable'] && $config['rewardpoints/using/rate'] && $model->offsetGet('customer_id')) {
            $additional = $model['additional'] ? json_decode($model['additional'], true) : [];
            $points = $this->getPoints($model);
            $additional['rewardpoints'] = $count === false ? $points : min($count, array_sum($points));
            $model->setData(['additional' => json_encode($additional)]);
        }
    }

    public function cancel($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        if ($config['rewardpoints/general/enable'] && $config['rewardpoints/using/rate'] && $model->offsetGet('customer_id')) {
            $additional = $model['additional'] ? json_decode($model['additional'], true) : [];
            unset($additional['rewardpoints']);
            $model->setData(['additional' => json_encode($additional)]);
        }
    }

    public function calc($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        if ($config['rewardpoints/general/enable'] && $config['rewardpoints/using/rate'] && $model->offsetGet('customer_id')) {
            $additional = $model['additional'] ? json_decode($model['additional'], true) : [];
            if (!empty($additional['rewardpoints'])) {
                $points = $this->getPoints($model);
                $additional['rewardpoints'] = min($additional['rewardpoints'], array_sum($points));
                $discount = $additional['rewardpoints'] * $config['rewardpoints/using/rate'];
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
        if ($config['rewardpoints/general/enable'] && $config['rewardpoints/using/rate'] && $model->offsetGet('customer_id')) {
            $additional = $model['additional'] ? json_decode($model['additional'], true) : [];
            if (!empty($additional['rewardpoints'])) {
                $record = new Record([
                    'customer_id' => $model->offsetGet('customer_id'),
                    'order_id' => $model->getId(),
                    'count' => -$additional['rewardpoints'],
                    'status' => 1,
                    'comment' => 'Consumption'
                ]);
                $record->save();
            }
        }
    }

    public function afterRefund($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        $order = $model->getOrder();
        if ($config['rewardpoints/general/enable'] && $config['rewardpoints/using/refund'] && $order && $order['additional']) {
            $additional = json_decode($order['additional'], true);
            if (!empty($additional['rewardpoints'])) {
                $collection = new Collection;
                $collection->columns(['id'])
                        ->where(['order_id' => $order->getId()])
                ->where->lessThan('count', 0);
                if (count($collection)) {
                    $record = new Record;
                    $record->setData(['id' => $collection[0]['id'], 'status' => 0])
                            ->save();
                }
            }
        }
    }

}
