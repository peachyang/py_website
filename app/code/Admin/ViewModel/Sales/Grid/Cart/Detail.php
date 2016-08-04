<?php

namespace Seahinet\Admin\ViewModel\Sales\Grid;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Sales\Model\Collection\Cart\Item as Collection;

class Detail extends Grid
{

    protected $translateDomain = 'sales';

    public function getRowLink($item)
    {
        return $this->getBaseUrl(':ADMIN/sales_cart/detail/?id=' . $item['id']);
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
        $collection->columns(['warehouse_id', 'product_name', 'store_id', 'options', 'qty', 'sku', 'total', 'status'])
                ->where([ 'cart_id' => $this->getQuery('id')]);
        return parent::prepareCollection($collection);
    }

}
