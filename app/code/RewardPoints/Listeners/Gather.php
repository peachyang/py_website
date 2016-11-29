<?php

namespace Seahinet\RewardPoints\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\RewardPoints\Model\Record;

class Gather implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function afterReview($event)
    {
        $config = $this->getContainer()->get('config');
        if ($config['rewardpoints/general/enable'] && ($points = $config['rewardpoints/gathering/reviewing'])) {
            $model = $event['model'];
            if ($event['isNew'] && $model->offsetGet('customer_id') && $model->offsetGet('order_id')) {
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
    }

    private function getPoints($order)
    {
        $config = $this->getContainer()->get('config');
        $total = ($order['base_subtotal'] + ($config['rewardpoints/gathering/calculation'] ? $order['base_shipping'] + $order['base_tax'] : 0));
        $max = (int) $config['rewardpoints/gathering/max_amount'];
        $calc = $total * $config['rewardpoints/gathering/rate'];
        return $total >= $config['rewardpoints/gathering/min_amount'] ? ($max ? min($max, $calc) : $calc) : 0;
    }

    public function afterOrderPlace()
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        if ($config['rewardpoints/general/enable'] && $config['rewardpoints/gathering/rate'] && $model->offsetGet('customer_id')) {
            $record = new Record([
                'customer_id' => $model->offsetGet('customer_id'),
                'order_id' => $model->getId(),
                'count' => $this->getPoints($model),
                'comment' => 'Consumption',
                'status' => 0
            ]);
            $record->save();
        }
    }

}
