<?php

namespace Seahinet\Promotion\Listeners;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Promotion\Model\Collection\Rule;

class Regular implements ListenerInterface
{

    protected $model;
    protected $stores = [];

    public function calc($event)
    {
        $this->model = $event['model'];
        if ($this->model instanceof \Seahinet\Sales\Model\Cart) {
            foreach ($this->model->getItems(true) as $item) {
                $this->stores[$item->offsetGet('store_id')] = 1;
            }
        } else {
            foreach ($this->model->getItems(true) as $item) {
                $this->stores[$this->model->offsetGet('store_id')] = 1;
            }
        }
        $result = 0;
        foreach ($this->stores as $storeId => $i) {
            $rules = new Rule;
            $rules->where(['status' => 1])
                    ->where('(store_id IS NULL OR store_id = ' . $storeId . ')')
                    ->order('sort_order');
            $block = false;
            foreach ($rules as $rule) {
                if ($this->matchRule($rule, $storeId)) {
                    $result += $this->handleRule($rule, $block);
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
        return $rule->getCondition()->match($this->model, $storeId);
    }

    protected function handleRule($rule, &$block)
    {
        if ($rule['stop_processing']) {
            $block = true;
        }
        if ($rule['free_shipping']) {
            $this->model->setData('free_shipping', 1);
        }
        return max(-($rule['apply_to'] ? $this->model['base_subtotal'] : $this->model['base_total']), $rule['is_fixed'] ? $rule['price'] : ($rule['apply_to'] ? $this->model['base_subtotal'] : $this->model['base_total']) * $rule['price']);
    }

}
