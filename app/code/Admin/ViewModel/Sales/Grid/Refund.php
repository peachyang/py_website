<?php

namespace Seahinet\Admin\ViewModel\Sales\Grid;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Customer\Model\Customer;
use Seahinet\Sales\Model\Collection\Rma as Collection;
use Seahinet\Sales\Source\Refund\Service;

class Refund extends Grid
{

    protected $translateDomain = 'sales';
    protected $action = ['getViewAction' => 'Admin\\Sales\\Refund::view'];

    public function getViewAction($item)
    {
        return '<a href="' . $this->getAdminUrl('sales_refund/view/?id=') . $item['id'] . '" title="' . $this->translate('View') .
                '"><span class="fa fa-fw fa-search" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('View') . '</span></a>';
    }

    protected function prepareColumns()
    {
        return [
            'id' => [
                'label' => 'ID',
            ],
            'order_increment_id' => [
                'label' => 'Order ID',
            ],
            'customer_name' => [
                'label' => 'Customer',
            ],
            'service' => [
                'label' => 'Service',
                'type' => 'select',
                'options' => (new Service)->getSourceArray()
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'options' => [
                    -1 => 'Refused',
                    0 => 'Applied',
                    1 => 'Approved',
                    2 => 'Delivering',
                    3 => 'Processing',
                    4 => 'Delivering',
                    5 => 'Complete'
                ]
            ],
            'updated_at' => [
                'label' => 'Last Modified',
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        $collection->join('sales_order', 'sales_rma.order_id=sales_order.id', ['order_increment_id' => 'increment_id'], 'left');
        $collection = parent::prepareCollection($collection);
        foreach ($collection as $key => $refund) {
            $collection[$key]['customer_name'] = (new Customer())->load($refund['customer_id'])['username'];
        }
        if (!$this->getQuery('desc')) {
            $this->query['desc'] = 'sales_rma.created_at';
        }
        return $collection;
    }

}
