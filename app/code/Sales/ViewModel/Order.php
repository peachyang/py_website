<?php

namespace Seahinet\Sales\ViewModel;

use Seahinet\Customer\ViewModel\Account;
use Seahinet\Sales\Model\Collection\Order as Collection;

class Order extends Account
{

    public function getOrders()
    {
        $collection = new Collection;
        $collection->where(['sales_order.customer_id' => $this->getCustomer()->getId()])
                ->order('created_at DESC');
        $condition = $this->getQuery();
        if (isset($condition['status'])) {
            $collection->join('sales_order_status', 'sales_order_status.id=sales_order.status_id', [], 'left')
                    ->join('sales_order_phase', 'sales_order_status.phase_id=sales_order_phase.id', [], 'left');
            if ($condition['status'] == 1) {
                $collection->where('(sales_order_phase.code=\'pending_payment\' OR sales_order_phase.code=\'pending\')');
            } else if ($condition['status'] == 2) {
                $collection->where([
                    'sales_order_phase.code' => 'complete',
                    'sales_order_status.is_default' => 1
                ]);
            } else if ($condition['status'] == 3) {
                $collection->join('review', 'review.order_id=sales_order.id', [], 'left')
                        ->where([
                            'sales_order_phase.code' => 'complete',
                            'sales_order_status.is_default' => 0
                        ])->having(['count(review.id)=0','sales_order.id is not null']);
            }
        }
        unset($condition['status']);
        $select = $collection->getSelect();
        if (isset($condition['limit']) && $condition['limit'] === 'all') {
            $select->reset('limit')->reset('offset');
        } else {
            $limit = $condition['limit'] ?? 10;
            if (isset($condition['page'])) {
                $select->offset(($condition['page'] - 1) * $limit);
                unset($condition['page']);
            }
            $select->limit((int) $limit);
        }
        unset($condition['limit']);
        if (isset($condition['asc'])) {
            $select->order((strpos($condition['asc'], ':') ?
                            str_replace(':', '.', $condition['asc']) :
                            $condition['asc']) . ' ASC');
            unset($condition['asc'], $condition['desc']);
        } else if (isset($condition['desc'])) {
            $select->order((strpos($condition['desc'], ':') ?
                            str_replace(':', '.', $condition['desc']) :
                            $condition['desc']) . ' DESC');
            unset($condition['desc']);
        }
        foreach ($condition as $key => $value) {
            if (trim($value) === '') {
                unset($condition[$key]);
            } else if (strpos($key, ':')) {
                if (strpos($value, '%') !== false) {
                    $select->where->like(str_replace(':', '.', $key), $value);
                } else {
                    $condition[str_replace(':', '.', $key)] = $value;
                }
                unset($condition[$key]);
            } else if (strpos($value, '%') !== false) {
                $select->where->like($key, $value);
                unset($condition[$key]);
            }
        }
        $select->where($condition);
        return $collection;
    }

    public function getLatestOrder()
    {
        $orders = $this->getOrders();
        return $orders->count() ? $orders[0] : false;
    }

}
