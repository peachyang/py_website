<?php

namespace Seahinet\Admin\ViewModel\Sales\Grid;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Collection\Shipment as Collection;
use Seahinet\Sales\Source\Shipment\Status;

class Shipment extends Grid
{

    protected $viewUrl = null;
    protected $printUrl = null;
    protected $action = ['getViewAction', 'getPrintAction'];
    protected $translateDomain = 'sales';

    public function getViewAction($item)
    {
        return '<a href="' . $this->getViewUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('View') .
                '"><span class="fa fa-fw fa-search" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('View') . '</span></a>';
    }

    public function getViewUrl()
    {
        if (is_null($this->viewUrl)) {
            $this->viewUrl = $this->getAdminUrl('sales_shipment/view/');
        }
        return $this->viewUrl;
    }

    public function getPrintAction($item)
    {
        return '<a href="' . $this->getPrintUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Print') .
                '"><span class="fa fa-fw fa-print" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Print') . '</span></a>';
    }

    public function getPrintUrl()
    {
        if (is_null($this->printUrl)) {
            $this->printUrl = $this->getAdminUrl('sales_shipment/print/');
        }
        return $this->printUrl;
    }

    protected function prepareColumns()
    {
        return [
            'increment_id' => [
                'label' => 'ID'
            ],
            'order_increment_id' => [
                'label' => 'Order ID',
                'sortby' => 'sales_order:increment_id'
            ],
            'shipping_method' => [
                'label' => 'Shipping Method'
            ],
            'billing_address' => [
                'label' => 'Billing Address'
            ],
            'shipping_address' => [
                'label' => 'Shipping Address'
            ],
            'warehouse_id' => [
                'label' => 'Warehouse'
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
                'label' => 'Last Modified'
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        $collection->join('sales_order', 'sales_order.id=sales_order_shipment.order_id', ['order_increment_id' => 'increment_id']);
        $user = (new Segment('admin'))->get('user');
        if ($user->getStore()) {
            $collection->where(['store_id' => $user->getStore()->getId()]);
        }
        if (!$this->getQuery('asc') && !$this->getQuery('desc')) {
            $collection->order('sales_order_shipment.created_at DESC');
        }
        return parent::prepareCollection($collection);
    }

}
