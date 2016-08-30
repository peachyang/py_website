<?php

namespace Seahinet\Promotion\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Promotion\Model\Collection\Rule;

class Regular implements ListenerInterface
{

    protected $model;
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
        foreach ($this->items as $storeId => $i) {
            $rules = new Rule;
            $rules->where(['status' => 1])
                    ->order('sort_order');
            $block = false;
            foreach ($rules as $rule) {
                if ((empty($rule->offsetGet('store_id')) || in_array($storeId, (array) $rule->offsetGet('store_id'))) && $this->matchRule($rule, $storeId)) {
                    $result += $this->handleRule($rule, $storeId, $block);
                    if ($block) {
                        break;
                    }
                }
            }
        }
        if ($result) {
            $this->model->setData([
                'base_discount' => $result,
                'discount' => $this->model->getCurrency()->convert($result, false),
                'discount_detail' => json_encode(['Promotion' => $result] + (json_decode($this->model['discount_detail'], true)? : []))
            ]);
        }
    }

    protected function matchRule($rule, $storeId)
    {
        if (!$rule['use_coupon'] || $rule->matchCoupon($this->model->getCoupon($storeId), $this->model)) {
            return $rule->getCondition()->match($this->model, $storeId);
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
                $discount = max(-$item['base_price'], $rule['is_fixed'] ? $rule['price'] : $item['base_price'] * $rule['price']);
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
            return max(-$total, $rule['is_fixed'] ? $rule['price'] : $total * $rule['price']);
        }
    }

}
