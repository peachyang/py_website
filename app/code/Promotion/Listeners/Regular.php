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
        $total = $this->model['base_subtotal'] + ($rule['apply_to'] ? 0 : (float) $this->model['base_shipping'] + (float) $this->model['base_tax']);
        $result = 0;
        if ($handler) {
            $items = $handler->matchItems($this->items[$storeId]);
        } else {
            $items = $this->items[$storeId];
        }
        if ($rule['free_shipping']) {
            $this->model->setData([
                'free_shipping' => 1,
                'base_shipping' => 0,
                'shipping' => 0
            ]);
        }
        $negative = 0;
        foreach ($items as $item) {
            $discount = $rule['is_fixed'] ? $rule['price'] : $item['base_price'] * $rule['price'] / 100;
            $qty = $rule['qty'] ? min($rule['qty'], $item['qty']) : $item['qty'];
            if ($discount > $item['base_price']) {
                $negative += ($discount - $item['base_price']) * $qty;
                $discount = $item['base_price'];
            }
            $result += $discount * $qty;
        }
        if (!$rule['apply_to'] && $negative) {
            $result += min($negative, (float) $this->model['base_shipping'] + (float) $this->model['base_tax']);
        }
        return min($total - $this->discount, $result);
    }

}
