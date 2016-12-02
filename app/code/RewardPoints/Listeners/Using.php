<?php

namespace Seahinet\RewardPoints\Listeners;

use Seahinet\RewardPoints\Model\Collection\Record as Collection;
use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Sales\Model\Cart;
use Zend\Db\Sql\Expression;

class Using implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    private function getPoints($model)
    {
        $config = $this->getContainer()->get('config');
        $collection = new Collection;
        $collection->columns(['amount' => new Expression('sum(count)')])
                ->where([
                    'customer_id' => $model->offsetGet('customer_id'),
                    'status' => 1
        ]);
        $balance = (count($collection) ? $collection[0]['amount'] : 0) - $config['rewardpoints/using/remain'];
        $total = [];
        $unavailable = [];
        if ($model instanceof Cart) {
            foreach ($model->getItem() as $item) {
                if ($item['product']['can_use_reward_points']) {
                    if (!isset($total[$item['store_id']])) {
                        $total[$item['store_id']] = 0;
                    }
                    $total[$item['store_id']] += $item['base_price'] * $item['qty'];
                } else {
                    if (!isset($unavailable[$item['store_id']])) {
                        $unavailable[$item['store_id']] = 0;
                    }
                    $unavailable += $item['base_price'] * $item['qty'];
                }
            }
        } else {
            $storeId = $model['store_id'];
            $total[$storeId] = 0;
            $unavailable[$storeId] = 0;
            foreach ($model->getItem() as $item) {
                if ($item['product']['can_use_reward_points']) {
                    $total[$storeId] += $item['base_price'] * $item['qty'];
                } else {
                    $unavailable[$storeId] += $item['base_price'] * $item['qty'];
                }
            }
        }
        $minAmount = $config['rewardpoints/using/min_amount'];
        $maxAmount = $config['rewardpoints/using/max_amount'];
        $maxAmountCalc = $config['rewardpoints/using/max_amount_calculation'];
        $rate = $config['rewardpoints/using/rate'];
        $calculation = $config['rewardpoints/using/calculation'];
        foreach ($total as $key => &$t) {
            $tmp = $t + (($calculation ? $model['base_shipping'] + $model['base_tax'] : 0) - $model['base_discount']) * $t / ($t + ($unavailable[$key] ?? 0));
            $max = ($maxAmountCalc ? ((int) ($t * $maxAmount / 100)) : ((int) $maxAmount)) / $rate;
            $t = $tmp >= $minAmount ? ($max ? min($balance, $max) : $balance) : 0;
        }
        return $total;
    }

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
                    'base_discount' => (float) $model->offsetGet('base_discount', false) - $discount,
                    'discount_detail' => json_encode([$config['rewardpoints/general/title'] => $discount] + (json_decode($model['discount_detail'], true) ?: []))
                ])->setData('discount', $model->getCurrency()->convert($model->offsetGet('base_discount')));
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
