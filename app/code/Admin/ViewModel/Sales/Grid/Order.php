<?php

namespace Seahinet\Admin\ViewModel\Sales\Grid;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Collection\Order as Collection;
use Seahinet\Sales\Source\Order\Status;

class Order extends Grid
{

    protected $viewUrl = null;
    protected $holdUrl = null;
    protected $unholdUrl = null;
    protected $cancelUrl = null;
    protected $printUrl = null;
    protected $action = ['getViewAction', 'getHoldAction', 'getUnholdAction', 'getCancelAction', 'getPrintAction'];
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
            $this->viewUrl = $this->getAdminUrl('sales_order/view/');
        }
        return $this->viewUrl;
    }

    public function getHoldAction($item)
    {
        return $item->canHold() ? ('<a href="' . $this->getHoldUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Hold') .
                '"><span class="fa fa-fw fa-pause-circle-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Hold') . '</span></a>') : false;
    }

    public function getHoldUrl()
    {
        if (is_null($this->holdUrl)) {
            $this->holdUrl = $this->getAdminUrl('sales_order/hold/');
        }
        return $this->holdUrl;
    }

    public function getUnholdAction($item)
    {
        return $item->canUnhold() ? ('<a href="' . $this->getUnholdUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Unhold') .
                '"><span class="fa fa-fw fa-play-circle-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Unhold') . '</span></a>') : false;
    }

    public function getUnholdUrl()
    {
        if (is_null($this->unholdUrl)) {
            $this->unholdUrl = $this->getAdminUrl('sales_order/unhold/');
        }
        return $this->unholdUrl;
    }

    public function getCancelAction($item)
    {
        return $item->canCancel() ? ('<a href="' . $this->getCancelUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Cancel') .
                '" onclick="if(!confirm(\'' . $this->translate('Are you sure to cancel this order?') .
                '\'))return false;"><span class="fa fa-fw fa-stop-circle-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Cancel') . '</span></a>') : false;
    }

    public function getCancelUrl()
    {
        if (is_null($this->cancelUrl)) {
            $this->cancelUrl = $this->getAdminUrl('sales_order/cancel/');
        }
        return $this->cancelUrl;
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
            $this->printUrl = $this->getAdminUrl('sales_order/print/');
        }
        return $this->printUrl;
    }

    protected function prepareColumns()
    {
        $currency = $this->getContainer()->get('currency');
        return [
            'increment_id' => [
                'label' => 'ID'
            ],
            'customer_id' => [
                'label' => 'Customer ID'
            ],
            'base_total' => [
                'label' => 'Total',
                'type' => 'price',
                'currency' => $currency
            ],
            'base_total_paid' => [
                'label' => 'Total Paid',
                'type' => 'price',
                'currency' => $currency
            ],
            'status_id' => [
                'label' => 'Status',
                'type' => 'select',
                'options' => (new Status)->getSourceArray()
            ],
            'created_at' => [
                'label' => 'Ordered Date'
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        $collection->join('sales_order_status', 'sales_order_status.id=sales_order.status_id', [])
                ->join('sales_order_phase', 'sales_order_status.phase_id=sales_order_phase.id', ['phase' => 'code']);
        $user = (new Segment('admin'))->get('user');
        if ($user->getStore()) {
            $collection->where(['store_id' => $user->getStore()->getId()]);
        }
        if (!$this->getQuery('asc') && !$this->getQuery('desc')) {
            $collection->order('created_at DESC');
        }
        return parent::prepareCollection($collection);
    }

}
