<?php

namespace Seahinet\RewardPoints\Listeners;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\RewardPoints\Model\Collection\Record as Collection;
use Seahinet\RewardPoints\Model\Record;
use Seahinet\Sales\Model\Collection\Order\Status\History;
use Zend\Db\Sql\Expression;

class Gather implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function afterReview($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        if ($config['rewardpoints/general/enable'] &&
                ($points = $config['rewardpoints/gathering/reviewing']) &&
                $event['isNew'] && $model->offsetGet('customer_id') &&
                $model->offsetGet('order_id')) {
            $limits = $config['rewardpoints/gathering/words_limitation'];
            if (!$limits || count(explode(' ', preg_replace('/[^\x00-\x7F]{3}/', ' ', preg_replace('/\s+/', ' ', trim(@gzdecode($model->offsetGet('content'))))))) > $limits) {
                $record = new Record([
                    'customer_id' => $model->offsetGet('customer_id'),
                    'count' => $points,
                    'comment' => 'Reviewing Product',
                    'status' => 1
                ]);
                $record->save();
            }
        }
    }

    public function afterRegister($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        if ($config['rewardpoints/general/enable'] && ($points = $config['rewardpoints/gathering/registration']) && $event['isNew']) {
            $record = new Record([
                'customer_id' => $model->getId(),
                'count' => $points,
                'comment' => 'Registration',
                'status' => 1
            ]);
            $record->save();
        }
    }

    private function getPoints($order)
    {
        $config = $this->getContainer()->get('config');
        $total = 0;
        $unavailable = 0;
        $points = 0;
        foreach ($order->getItems() as $item) {
            if ($item['product']['reward_points'] > 0) {
                $points += $item['product']['reward_points'] * $item['qty'];
            } else if (is_null($item['product']['reward_points']) || $item['product']['reward_points'] === '') {
                $total += $item['base_price'] * $item['qty'];
            } else {
                $unavailable += $item['base_price'] * $item['qty'];
            }
        }
        if ($total + $unavailable == 0) {
            return 0;
        }
        $total += (($config['rewardpoints/gathering/calculation'] ? $order['base_shipping'] + $order['base_tax'] : 0) + $order['base_discount']) * $total / ($total + $unavailable);
        $max = $config['rewardpoints/gathering/max_amount_calculation'] ? ((int) ($total * $config['rewardpoints/gathering/max_amount'] / 100)) : ((int) $config['rewardpoints/gathering/max_amount']);
        $calc = $total * $config['rewardpoints/gathering/rate'] + $points;
        return $total >= $config['rewardpoints/gathering/min_amount'] ? ($max ? min($max, $calc) : $calc) : 0;
    }

    public function afterOrderPlace($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        if ($config['rewardpoints/general/enable'] && $config['rewardpoints/gathering/rate'] && $model->offsetGet('customer_id') && ($points = $this->getPoints($model))) {
            $record = new Record([
                'customer_id' => $model->offsetGet('customer_id'),
                'order_id' => $model->getId(),
                'count' => $points,
                'comment' => 'Consumption',
                'status' => 0
            ]);
            $record->save();
        }
    }

    public function afterOrderComplete($event)
    {
        if ($this->getContainer()->get('config')['rewardpoints/general/activating'] == 0) {
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
                    $collection = new Collection;
                    $collection->columns(['id'])
                            ->where(['order_id' => $model->getId()])
                    ->where->greaterThan('count', 0);
                    if (count($collection)) {
                        $record = new Record;
                        $record->load($collection[0]['id']);
                        $record->setData('status', 1)->save();
                    }
                }
            }
        }
    }

    public function afterSubscribe($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        if ($event['isNew'] && $config['rewardpoints/general/enable'] && ($points = $config['rewardpoints/gathering/newsletter']) && $model['status'] === 1) {
            $customer = new Customer;
            $customer->load($model['email'], 'email');
            if ($customer->getId()) {
                $record = new Record([
                    'customer_id' => $customer->getId(),
                    'count' => $points,
                    'comment' => 'Newsletter Signup',
                    'status' => 1
                ]);
                $record->save();
            }
        }
    }

    public function afterShare($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        if ($event['isNew'] && $config['rewardpoints/general/enable'] && ($points = $config['rewardpoints/gathering/share'])) {
            $collection = new Collection;
            $collection->columns(['amount' => new Expression('sum(count)')])
                    ->where([
                        'customer_id' => $model->offsetGet('customer_id'),
                        'status' => 1,
                        'comment' => 'Sharing to Social Medias'
                    ])->group('comment');
            if (!($limit = $config['rewardpoints/gathering/share_limitation']) ||
                    !count($collection->load(true, true)) ||
                    $collection[0]['amount'] < $limit) {
                $record = new Record([
                    'customer_id' => $model->offsetGet('customer_id'),
                    'count' => $limit ? min($limit - $collection[0]['amount'], $points) : $points,
                    'comment' => 'Sharing to Social Medias',
                    'status' => 1
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
        if ($config['rewardpoints/general/enable'] && $config['rewardpoints/gathering/refund'] && $order) {
            $additional = json_decode($order['additional'], true);
            if (!empty($additional['rewardpoints'])) {
                $collection = new Collection;
                $collection->columns(['id'])
                        ->where(['order_id' => $order->getId()])
                ->where->greaterThan('count', 0);
                if (count($collection)) {
                    $record = new Record;
                    $record->setData(['id' => $collection[0]['id'], 'status' => 0])
                            ->save();
                }
            }
        }
    }

    public function beforeSaveCustomer($event)
    {
        unset($event['model']['rewardpoints']);
    }

    public function afterSaveBackendCustomer($event)
    {
        $customer = $event['model'];
        if ($count = (int) $customer->offsetGet('adjust_rewardpoints')) {
            $record = new Record;
            $record->setData([
                'customer_id' => $customer->getId(),
                'count' => $count,
                'comment' => 'System Adjustment',
                'status' => 1
            ]);
            $record->save();
        }
    }

}
