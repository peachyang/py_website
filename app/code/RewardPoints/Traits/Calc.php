<?php

namespace Seahinet\RewardPoints\Traits;

use Seahinet\RewardPoints\Model\Collection\Record as Collection;
use Seahinet\Sales\Model\Cart;
use Zend\Db\Sql\Expression;

trait Calc
{

    protected function getPoints($model)
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
            foreach ($model->getItems() as $item) {
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
            foreach ($model->getItems() as $item) {
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
            $t = $tmp >= $minAmount ? ($max ? min($max, $tmp / $rate) : min($tmp / $rate)) : 0;
        }
        return min($balance, array_sum($total));
    }

}
