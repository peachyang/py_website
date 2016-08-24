<?php

namespace Seahinet\Admin\ViewModel\Sales\Grid;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Sales\Model\Collection\Rma as Collection;
use Seahinet\Customer\Model\Customer;

class Refund extends Grid
{

    protected $viewUrl = '';
    protected $printUrl = '';
    protected $translateDomain = 'sales';
    protected $action = ['getViewAction', 'getPrintAction'];

    public function getViewAction($item)
    {
        return '<a href="' . $this->getViewUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('View') .
                '"><span class="fa fa-fw fa-search" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('View') . '</span></a>';
    }

    public function getPrintAction($item)
    {
        return '<a href="' . $this->getPrintUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Print') .
        '"><span class="fa fa-fw fa-print" aria-hidden="true"></span><span class="sr-only">' .
        $this->translate('Print') . '</span></a>';
    }
    
    public function getPrintUrl()
    {
        if ($this->printUrl == '') {
            $this->printUrl = $this->getAdminUrl('sales_refund/print/');
        }
        return $this->printUrl;
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
        $collection->join('sales_order', 'rma.order_id=sales_order.id', ['order_increment_id'=>'increment_id'], 'left');
        foreach ($collection as $key=>$refund){
            $collection[$key]['customer_name'] = (new Customer())->load($refund['customer_id'])['username'];
        }
        return $collection;
    }

}
