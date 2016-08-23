<?php

namespace Seahinet\Admin\ViewModel\Sales\Grid;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Sales\Model\Collection\Rma as Collection;
use Seahinet\Customer\Model\Customer;

class Refund extends Grid
{

    protected $viewUrl = '';
    protected $deleteUrl = '';
    protected $translateDomain = 'sales';
    protected $action = ['getViewAction', 'getDeleteAction'];

    public function getViewAction($item)
    {
        return '<a href="' . $this->getViewUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('View') .
                '"><span class="fa fa-fw fa-search" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('View') . '</span></a>';
    }

    public function getDeleteAction($item)
    {
        return $item['is_default'] ? false : '<a href="' . $this->getDeleteUrl() .
                '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-fw fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    public function getViewUrl()
    {
        if (is_null($this->viewUrl)) {
            $this->viewUrl = $this->getAdminUrl('sales_refund/view/');
        }
        return $this->viewUrl;
    }

    public function getDeleteUrl()
    {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/sales_refund/delete/');
        }
        return $this->deleteUrl;
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
