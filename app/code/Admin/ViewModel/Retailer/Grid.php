<?php

namespace Seahinet\Admin\ViewModel\Retailer;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\Retailer\Model\Collection\Application as Collection;

class Grid extends PGrid
{

    protected $editUrl = '';
    protected $translateDomain = 'retailer';

    public function getRowLink($item)
    {
        return $this->getAdminUrl(':ADMIN/retailer_apply/edit/?id=' . $item->getId());
    }

    protected function prepareColumns()
    {
        return [
            'customer_id' => [
                'label' => 'Customer ID',
                'use4sort' => false
            ],
            'phone' => [
                'label' => 'Phone Number',
                'use4sort' => false
            ],
            'brand_type' => [
                'label' => 'Brand',
                'use4sort' => false,
                'type' => 'select',
                'options' => [
                    'Agency',
                    'Own'
                ]
            ],
            'product_type' => [
                'label' => 'Product Type',
                'use4sort' => false
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'use4sort' => false,
                'options' => [
                    'Disabled',
                    'Enabled'
                ]
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        $collection->order('status ASC, customer_id DESC');
        return parent::prepareCollection($collection);
    }

}
