<?php

namespace Seahinet\Admin\ViewModel\Sales\Grid;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Collection\Invoice as Collection;

class Invoice extends Grid
{

    protected $action = [
        'getViewAction' => 'Admin\\Sales\\Invoice::view',
        'getPrintAction' => 'Admin\\Sales\\Invoice::print'
    ];
    protected $translateDomain = 'sales';

    public function getViewAction($item)
    {
        return '<a href="' . $this->getAdminUrl('sales_invoice/view/?id=') . $item['id'] . '" title="' . $this->translate('View') .
                '"><span class="fa fa-fw fa-search" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('View') . '</span></a>';
    }

    public function getPrintAction($item)
    {
        return '<a href="' . $this->getAdminUrl('sales_invoice/print/?id=') . $item['id'] . '" title="' . $this->translate('Print') .
                '"><span class="fa fa-fw fa-print" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Print') . '</span></a>';
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
                'type' => 'price',
                'currency' => $currency
            ],
            'created_at' => [
                'label' => 'Invoiced Date'
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        $collection->join('sales_order', 'sales_order.id=sales_order_invoice.order_id', ['order_increment_id' => 'increment_id']);
        $user = (new Segment('admin'))->get('user');
        if ($user->getStore()) {
            $collection->where(['store_id' => $user->getStore()->getId()]);
        }
        if (!$this->getQuery('desc')) {
            $this->query['desc'] = 'sales_order_invoice.created_at';
        }
        return parent::prepareCollection($collection);
    }

}
