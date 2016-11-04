<?php

namespace Seahinet\Admin\ViewModel\Sales\Grid;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Sales\Model\Collection\Rma as Collection;
use Seahinet\Customer\Model\Customer;

class Refund extends Grid
{

    protected $viewUrl = '';
    protected $holdUrl = '';
    protected $unholdUrl = '';
    protected $translateDomain = 'sales';
    protected $action = ['getViewAction', 'getHoldAction', 'getUnholdAction'];

    public function getViewAction($item)
    {
        return '<a href="' . $this->getViewUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('View') .
                '"><span class="fa fa-fw fa-search" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('View') . '</span></a>';
    }

    public function getHoldAction($item)
    {
        return $item['status'] == 1 ? ('<a href="' . $this->getHoldUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Processing') .
                '"><span class="fa fa-fw fa-pause-circle-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Processing') . '</span></a>') : false;
    }

    public function getHoldUrl()
    {
        if ($this->holdUrl == '') {
            $this->holdUrl = $this->getAdminUrl('sales_refund/processing/');
        }
        return $this->holdUrl;
    }

    public function getUnholdAction($item)
    {
        return $item['status'] == 0 ? ('<a href="' . $this->getUnholdUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Complete') .
                '"><span class="fa fa-fw fa-play-circle-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Complete') . '</span></a>') : false;
    }

    public function getUnholdUrl()
    {
        if ($this->unholdUrl == '') {
            $this->unholdUrl = $this->getAdminUrl('sales_refund/complete/');
        }
        return $this->unholdUrl;
    }

    public function getViewUrl()
    {
        if ($this->viewUrl === '') {
            $this->viewUrl = $this->getAdminUrl('sales_refund/view/');
        }
        return $this->viewUrl;
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
            'carrier' => [
                'label' => 'Carrier',
            ],
            'track_number' => [
                'label' => 'Track Number',
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'options' => [
                    1 => 'Complete',
                    0 => 'Processing'
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
        foreach ($collection as $key => $refund) {
            $collection[$key]['customer_name'] = (new Customer())->load($refund['customer_id'])['username'];
        }
        return $collection;
    }

}
