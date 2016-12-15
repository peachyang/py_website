<?php

namespace Seahinet\Admin\ViewModel\Sales\Grid;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Collection\CreditMemo as Collection;

class CreditMemo extends Grid
{

    protected $viewUrl = null;
    protected $holdUrl = null;
    protected $unholdUrl = null;
    protected $cancelUrl = null;
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
            $this->viewUrl = $this->getAdminUrl('sales_creditmemo/view/');
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
            $this->printUrl = $this->getAdminUrl('sales_creditmemo/print/');
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
            'order_increment_id' => [
                'label' => 'Order ID',
                'sortby' => 'sales_order:increment_id'
            ],
            'base_total' => [
                'label' => 'Total',
                'currency' => $currency,
                'type' => 'price'
            ],
            'warehouse_id' => [
                'label' => 'Warehouse',
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
        $collection->join('sales_order', 'sales_order.id=sales_order_creditmemo.order_id', ['order_increment_id' => 'increment_id']);
        $user = (new Segment('admin'))->get('user');
        if ($user->getStore()) {
            $collection->where(['store_id' => $user->getStore()->getId()]);
        }
        if (!$this->getQuery('desc')) {
            $this->query['desc'] = 'sales_order_creditmemo.created_at';
        }
        return parent::prepareCollection($collection);
    }

}
