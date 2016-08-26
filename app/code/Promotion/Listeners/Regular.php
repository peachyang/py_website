<?php

namespace Seahinet\Promotion\Listeners;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Promotion\Model\Collection\Rule;

class Regular implements ListenerInterface
{

    protected $model;
    protected $customer;
    protected $items = [];

    public function calc($event)
    {
        $this->model = $event['model'];
        $this->customer = new Customer;
        $this->customer->load($this->model->offsetGet('customer_id'));
        if ($this->model instanceof \Seahinet\Sales\Model\Cart) {
            foreach ($this->model->getItems(true) as $item) {
                if (!isset($this->items[$item->offsetGet('store_id')])) {
                    $this->items[$item->offsetGet('store_id')] = [];
                }
                $this->items[$item->offsetGet('store_id')][] = $item;
            }
        } else {
            foreach ($this->model->getItems(true) as $item) {
                if (!isset($this->items[$this->model->offsetGet('store_id')])) {
                    $this->items[$this->model->offsetGet('store_id')] = [];
                }
                $this->items[$this->model->offsetGet('store_id')][] = $item;
            }
        }
        $result = 0;
        foreach ($this->items as $storeId => $items) {
            $rules = new Rule;
            $rules->where(['store_id' => $storeId]);
            foreach ($rules as $rule) {
                if ($this->matchRule($rule)) {
                    $result += $this->handleRule($rule);
                }
            }
        }
        if ($result) {
            $this->model->setData([
                'base_discount' => $result,
                'discount' => $this->model->getCurrency()->convert($result, false),
                'discount_detail' => json_encode(['Promotion' => $result] + json_decode($this->model['discount_detail'], true))
            ]);
        }
    }

    protected function matchRule($rule)
    {
        foreach ($rule->getCondition() as $condition) {
            
        }
        return false;
    }

    protected function handleRule($rule)
    {
        
    }

}
