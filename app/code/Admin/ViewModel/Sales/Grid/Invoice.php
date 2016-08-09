<?php

namespace Seahinet\Admin\ViewModel\Sales\Grid;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Collection\Invoice as Collection;
use Seahinet\Sales\Source\Invoice\Status;

class Invoice extends Grid
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
            $this->viewUrl = $this->getAdminUrl('sales_invoice/view/');
        }
        return $this->viewUrl;
    }

    public function getHoldAction($item)
    {
        return $item['phase'] === 'processing' ? ('<a href="' . $this->getHoldUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Hold') .
                '"><span class="fa fa-fw fa-pause-circle-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Hold') . '</span></a>') : false;
    }

    public function getHoldUrl()
    {
        if (is_null($this->holdUrl)) {
            $this->holdUrl = $this->getAdminUrl('sales_invoice/hold/');
        }
        return $this->holdUrl;
    }

    public function getUnholdAction($item)
    {
        return $item['phase'] === 'holded' ? ('<a href="' . $this->getUnholdUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Unhold') .
                '"><span class="fa fa-fw fa-play-circle-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Unhold') . '</span></a>') : false;
    }

    public function getUnholdUrl()
    {
        if (is_null($this->unholdUrl)) {
            $this->unholdUrl = $this->getAdminUrl('sales_invoice/unhold/');
        }
        return $this->unholdUrl;
    }

    public function getCancelAction($item)
    {
        return in_array($item['phase'], ['pending', 'pending_payment']) ? ('<a href="' . $this->getCancelUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Cancel') .
                '"><span class="fa fa-fw fa-stop-circle-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Cancel') . '</span></a>') : false;
    }

    public function getCancelUrl()
    {
        if (is_null($this->cancelUrl)) {
            $this->cancelUrl = $this->getAdminUrl('sales_invoice/cancel/');
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
            $this->printUrl = $this->getAdminUrl('sales_invoice/print/');
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
            'order_id' => [
                'label' => 'Order ID',
                'currency' => $currency
            ],
            'base_total' => [
                'label' => 'Total',
                'type' => 'price',
                'currency' => $currency
            ],
            'updated_at' => [
                'label' => 'Last Modified'
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        $user = (new Segment('admin'))->get('user');
        if ($user->getStore()) {
            $collection->where(['store_id' => $user->getStore()->getId()]);
        }
        if (!$this->getQuery('asc') && !$this->getQuery('desc')) {
            $collection->order('updated_at DESC, created_at DESC');
        }
        return parent::prepareCollection($collection);
    }

}
