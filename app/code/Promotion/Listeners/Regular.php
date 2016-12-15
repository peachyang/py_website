<?php

namespace Seahinet\Promotion\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Promotion\Model\Collection\Rule;

class Regular implements ListenerInterface
{

    protected $model;
    protected $discount = 0;
    protected $items = [];

    public function calc($event)
    {
        $this->model = $event['model'];
        if ($this->model instanceof \Seahinet\Sales\Model\Cart) {
            foreach ($this->model->getItems(true) as $item) {
                if (!isset($this->items[$item->offsetGet('store_id')])) {
                    $this->items[$item->offsetGet('store_id')] = [];
                }
                $this->items[$item->offsetGet('store_id')][$item['id']] = $item;
            }
        } else {
            foreach ($this->model->getItems(true) as $item) {
                if (!isset($this->items[$this->model->offsetGet('store_id')])) {
                    $this->items[$this->model->offsetGet('store_id')] = [];
                }
                $this->items[$this->model->offsetGet('store_id')][$item['id']] = $item;
            }
        }
        $result = 0;
        $time = time();
        $rules = new Rule;
        $rules->where(['status' => 1])
                ->order('sort_order');
        $this->discount = -$this->model->offsetGet('base_discount');
        foreach ($this->items as $storeId => $i) {
            $block = false;
            foreach ($rules as $rule) {
                if ((empty($rule->offsetGet('store_id')) || in_array($storeId, (array) $rule->offsetGet('store_id'))) &&
                        (empty($rule->offsetGet('from_date')) || $time >= strtotime($rule->offsetGet('from_date'))) &&
                        (empty($rule->offsetGet('to_date')) || $time <= strtotime($rule->offsetGet('to_date'))) &&
                        $this->matchRule($rule, $storeId)) {
                    $discount = $this->handleRule($rule, $storeId, $block);
                    $result += $discount;
                    $this->discount += $discount;
                    if ($block) {
                        $event->stopPropagation();
                        break;
                    }
                }
            }
        }
        if ($result) {
            $this->model->setData([
                'base_discount' => (float) $this->model->offsetGet('base_discount') - $result,
                'discount_detail' => json_encode(['Promotion' => - $result] + (json_decode($this->model['discount_detail'], true) ?: []))
            ])->setData('discount', $this->model->getCurrency()->convert($this->model->offsetGet('base_discount')));
        }
    }

    protected function matchRule($rule, $storeId)
    {
        if (!$rule['use_coupon'] || $rule->matchCoupon($this->model->getCoupon($storeId), $this->model)) {
            return $rule->getCondition() ? $rule->getCondition()->match($this->model, $storeId) : true;
        }
        return false;
    }

    protected function handleRule($rule, $storeId, &$block)
    {
        if ($rule['stop_processing']) {
            $block = true;
        }
        $handler = $rule->getHandler();
        if ($handler) {
            $count = count($this->items[$storeId]);
            $items = $handler->matchItems($this->items[$storeId]);
            $total = $rule['apply_to'] ? 0 : (float) $this->model['base_shipping'] + (float) $this->model['base_tax'];
            if ($rule['free_shipping'] && $count === count($items)) {
                $this->model->setData([
                    'free_shipping' => 1,
                    'base_shipping' => 0,
                    'shipping' => 0
                ]);
            }
            foreach ($items as $item) {
                if ($rule['free_shipping']) {
                    $item->setData([
                        'free_shipping' => 1,
                        'base_shipping' => 0,
                        'shipping' => 0
                    ]);
                }
                $discount = min($item['base_price'], $rule['is_fixed'] ? $rule['price'] : $item['base_price'] * $rule['price'] / 100);
                if ($rule['qty']) {
                    $discount *= min($rule['qty'], $item['qty']);
                } else {
                    $discount *= $item['qty'];
                }
                $item->setData([
                    'base_discount' => $discount,
                    'discount' => $this->model->getCurrency()->convert($discount, false)
                ])->collateTotals()->save();
                $total += $item['base_total'];
            }
            return $total;
        } else {
            if ($rule['free_shipping']) {
                $this->model->setData([
                    'free_shipping' => 1,
                    'base_shipping' => 0,
                    'shipping' => 0
                ]);
            }
            $total = $this->model['base_subtotal'] + ($rule['apply_to'] ? 0 : (float) $this->model['base_shipping'] + (float) $this->model['base_tax']);
            return min($total - $this->discount, $rule['is_fixed'] ? $rule['price'] : $total * $rule['price'] / 100);
        }
    }

}
