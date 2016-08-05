<?php

namespace Seahinet\Admin\ViewModel\Sales\Grid;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Sales\Model\Collection\Cart as Collection;

class Cart extends Grid
{

    protected $translateDomain = 'sales';

    public function getRowLink($item)
    {
        return $this->getAdminUrl('sales_cart/detail/?id=' . $item['id']);
    }

    protected function prepareColumns()
    {
        return [
            'customer_id' => [
                'label' => 'Customer ID'
            ],
            'currency' => [
                'label' => 'Currency'
            ],
            'total' => [
                'label' => 'Total'
            ],
            'updated_at' => [
                'label' => 'Last Modified',
                'use4filter' => false
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        $expired = date('Y-m-d H:i:s', time() - 3600 * 72);
        $collection->columns(['id', 'customer_id', 'currency', 'total', 'updated_at'])
                ->where(['status' => 1])
//                ->where('((updated_at IS NULL AND created_at <= "' . $expired . '") OR (updated_at <= "' . $expired . '"))')
                ->order('updated_at DESC, created_at DESC')
                ->where->greaterThan('subtotal', 0);
        return parent::prepareCollection($collection);
    }

}
