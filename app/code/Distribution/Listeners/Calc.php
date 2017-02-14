<?php

namespace Seahinet\Distribution\Listeners;

use Seahinet\Customer\Model\{
    Balance,
    Customer
};
use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Retailer\Model\Retailer;
use Seahinet\RewardPoints\Model\Record;
use Seahinet\Sales\Model\Collection\Order\Status\History;

class Calc implements ListenerInterface
{

    private $rewardPointsLevels = null;
    private $balanceLevels = null;
    private $totalBalance = 0;

    public function afterOrderComplete($event)
    {
        $model = $event['model'];
        if ($model->getPhase()['code'] === 'complete') {
            $history = new History;
            $history->join('sales_order_status', 'sales_order_status.id=sales_order_status_history.status_id', [], 'left')
                    ->join('sales_order_phase', 'sales_order_phase.id=sales_order_status.phase_id', [], 'left')
                    ->where([
                        'order_id' => $model->getId(),
                        'sales_order_phase.code' => 'complete'
            ]);
            if (count($history->load(false, true)) === 0) {
                $config = $this->getContainer()->get('config');
                $this->totalBalance = $model->offsetGet('base_grand_total') / 100 * $config['distribution/general/percentage'];
                if ($model->offsetGet('customer_id')) {
                    $customer = new Customer;
                    $customer->load($model->offsetGet('customer_id'));
                    $this->totalBalance -= $this->handlerRewardPoints($model, $customer) * $config['rewardpoints/using/rate'];
                }
                if ($this->totalBalance) {
                    $retailer = new Retailer;
                    $retailer->load($model->offsetGet('store_id'), 'store_id');
                    $this->handlerBalance($model, $retailer);
                }
            }
        }
    }

    private function getRewardPointsLevels()
    {
        if (is_null($this->rewardPointsLevels)) {
            $config = $this->getContainer()->get('config');
            $count = $config['distribution/rewardpoints/level'];
            $sum = 0;
            $percentage = explode(',', $config['distribution/rewardpoints/percentage']);
            $this->rewardPointsLevels = [];
            foreach ($percentage as $level) {
                $sum += $level;
                $count --;
                if ($count && $sum < 100) {
                    $this->rewardPointsLevels[] = $level / 100;
                } else {
                    break;
                }
            }
        }
        return $this->rewardPointsLevels;
    }

    private function handlerRewardPoints($order, $customer, $level = 0)
    {
        if ($customer['referer'] && $level < count($this->getRewardPointsLevels())) {
            $config = $this->getContainer()->get('config');
            $parent = new Customer;
            $parent->load($customer['referer'], 'increment_id');
            if ($parent->getId()) {
                $points = (int) ($config['distribution/rewardpoints/total'] * $this->getRewardPointsLevels()[$level]);
                $record = new Record;
                $record->setData([
                    'customer_id' => $parent->getId(),
                    'order_id' => $order->getId(),
                    'count' => $points,
                    'comment' => 'Distribution',
                    'status' => $config['rewardpoints/general/activating'] ? 0 : 1
                ])->save();
                return $points + $this->handlerRewardPoints($order, $parent, $level + 1);
            }
        }
        return 0;
    }

    private function getBalanceLevels()
    {
        if (is_null($this->balanceLevels)) {
            $config = $this->getContainer()->get('config');
            $count = $config['distribution/balance/level'];
            $sum = 0;
            $percentage = explode(',', $config['distribution/balance/percentage']);
            $this->balanceLevels = [];
            foreach ($percentage as $level) {
                $sum += $level;
                $count --;
                if ($count && $sum < 100) {
                    $this->balanceLevels[] = $level / 100;
                } else {
                    break;
                }
            }
        }
        return $this->balanceLevels;
    }

    private function handlerBalance($order, $retailer, $level = 0)
    {
        $customer = new Customer;
        $customer->load($retailer->offsetGet('customer_id'));
        if ($customer->getId() && $retailer->getId() && $level < count($this->getBalanceLevels())) {
            $config = $this->getContainer()->get('config');
            $balance = new Balance;
            $balance->setData([
                'customer_id' => $retailer['customer_id'],
                'order_id' => $order->getId(),
                'count' => $this->totalBalance * $this->getBalanceLevels()[$level],
                'comment' => 'Distribution',
                'status' => $config['balance/general/activating'] ? 0 : 1
            ])->save();
            $parent = new Retailer;
            $parent->load($customer->offsetGet('store_id'), 'store_id');
            $this->handlerBalance($order, $retailer, $level + 1);
        }
    }

}
