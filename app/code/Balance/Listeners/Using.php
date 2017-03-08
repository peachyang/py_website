<?php

namespace Seahinet\Balance\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Customer\Model\Balance;
use Seahinet\Customer\Model\Collection\Balance as Collection;
use Seahinet\Customer\Model\Customer;

class Using implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Balance\Traits\Calc;

    public function apply($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        if ($config['balance/general/enable'] && $config['balance/general/product_for_recharge'] && $model->offsetGet('customer_id')) {
            $additional = $model['additional'] ? json_decode($model['additional'], true) : [];
            $points = $this->getBalances($model);
            $additional['balance'] = $points ? 1 : 0;
            $model->setData(['additional' => json_encode($additional)]);
        }
    }

    public function cleanBalance($event)
    {
        $model = $event['model'];
        $detail = json_decode($model->offsetGet('discount_detail'), true);
        if ($detail && !empty($detail['Balance'])) {
            $balance = $detail['Balance'];
            unset($detail['Balance']);
            $model->setData([
                'base_discount' => (float) $model->offsetGet('base_discount') - $balance,
                'discount_detail' => json_encode($detail)
            ])->setData('discount', $model->getCurrency()->convert($model->offsetGet('base_discount')));
        }
    }

    public function calc($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        if ($config['balance/general/enable'] && $config['balance/general/product_for_recharge'] && $model->offsetGet('customer_id')) {
            $additional = $model['additional'] ? json_decode($model['additional'], true) : [];
            if (!empty($additional['balance'])) {
                $discount = $this->getBalances($model, true);
                if (!empty($additional['rewardpoints']) || !empty($additional['promotion'])) {
                    $points = $this->getBalances($model, TRUE);
                    $additional['balance'] = ($model->offsetGet('base_subtotal') + $model->offsetGet('base_discount') ) + $model->offsetGet('base_shipping') + $model->offsetGet('base_tax');
                    $discount = min($additional['balance'], $points);
                    $model->setData([
                        'base_discount' => (float) $model->offsetGet('base_discount') - $discount,
                        'discount_detail' => json_encode(['Balance' => - $discount] + (json_decode($model['discount_detail'], true) ?: []))
                    ])->setData('discount', $model->getCurrency()->convert($model->offsetGet('base_discount')));
                } else {
                    $additional['balance'] = $model->offsetGet('base_subtotal') + $model->offsetGet('base_shipping') + $model->offsetGet('base_tax');
                    $discount = min($additional['balance'], $discount);
                    $model->setData([
                        'base_discount' => (float) $model->offsetGet('base_discount') - $discount,
                        'discount_detail' => json_encode(['Balance' => - $discount] + (json_decode($model['discount_detail'], true) ?: []))
                    ])->setData('discount', $model->getCurrency()->convert($model->offsetGet('base_discount')));
                }
            }
        }
    }

    public function cancel($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        if ($config['balance/general/enable'] && $config['balance/general/product_for_recharge'] && $model->offsetGet('customer_id')) {
            $additional = $model['additional'] ? json_decode($model['additional'], true) : [];
            unset($additional['balance']);
            $model->setData(['additional' => json_encode($additional)]);
        }
    }

    public function afterOrderPlace($event)
    {
        $config = $this->getContainer()->get('config');
        $model = $event['model'];
        if ($config['balance/general/enable'] && $config['balance/general/product_for_recharge'] && $model->offsetGet('customer_id')) {
            $points = (float) $model->getDiscount('Balance');
            if ($points && $model['base_discount'] < (json_decode($model['discount_detail'], true)['Promotion'] ?? 0)) {
                $record = new Balance([
                    'customer_id' => $model->offsetGet('customer_id'),
                    'order_id' => $model->getId(),
                    'amount' => $points,
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
        if ($config['balance/general/enable'] && $order && $order['additional']) {
            $additional = json_decode($order['additional'], true);
            if (!empty($additional['balance'])) {
                $collection = new Collection;
                $collection->columns(['id'])
                        ->where(['order_id' => $order->getId()])
                ->where->lessThan('amount', 0);
                if (count($collection)) {
                    $record = new Balance;
                    $record->setData(['id' => $collection[0]['id'], 'comment' => 'Balance Refund', 'status' => 0])
                            ->save();
                }
            }
        }
    }

    public function afterOrderCancel($event)
    {
        $model = $event['model'];
        if ($model->getPhase()['code'] === 'canceled') {
            $collection = new Collection;
            $collection->columns(['id'])
                    ->where(['order_id' => $model->getId()])
            ->where->lessThan('amount', 0);
            if (count($collection)) {
                $record = new Balance;
                    $record->load($collection[0]['id']);
                    $record->setData(['comment' => 'Order Cancelled', 'status' => 0])->save();
            }
        }
    }

}
