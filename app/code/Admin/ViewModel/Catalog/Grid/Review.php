<?php

namespace Seahinet\Admin\ViewModel\Catalog\Grid;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\Catalog\Model\Collection\Product\Review as Collection;
use Seahinet\Lib\Session\Segment;
use Seahinet\Catalog\Source\Product;
use Seahinet\Customer\Source\Customer;
use Seahinet\Lib\Source\Language;

class Review extends PGrid
{

    protected $action = [
        'getEditAction' => 'Admin\\Catalog\\Product\\Review::edit',
        'getDeleteAction' => 'Admin\\Catalog\\Product\\Review::delete'
    ];
    protected $translateDomain = 'review';

    public function getEditAction($item)
    {
        return '<a href="' . $this->getAdminUrl(':ADMIN/catalog_product_review/edit/?id=') . $item['id'] . '"title="' . $this->translate('Edit') .
                '"><span class="fa fa-fw fa-file-text-o" aria-hidden="true"></span><span class="sr-only">'
                . $this->translate('Edit') . '</span></a>';
    }

    public function getDeleteAction($item)
    {
        return '<a href="' . $this->getAdminUrl(':ADMIN/catalog_product_review/delete/') . '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-fw fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    protected function prepareColumns($columns = [])
    {
        return[
            'id' => [
                'type' => 'hidden',
                'label' => 'ID'
            ],
            'product_id' => [
                'type' => 'select',
                'label' => 'Product',
                'options' => (new Product)->getSourceArray()
            ],
            'customer_id' => [
                'type' => 'select',
                'label' => 'Customer',
                'options' => (new Customer)->getSourceArray()
            ],
            'language_id' => [
                'type' => 'select',
                'label' => 'Language',
                'options' => (new Language)->getSourceArray()
            ],
            'order_id' => [
                'type' => 'text',
                'label' => 'Order'
            ],
            'subject' => [
                'type' => 'text',
                'label' => 'Subject'
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'options' => [
                    1 => 'Enabled',
                    0 => 'Disabled'
                ]
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        if (!$this->getQuery('desc')) {
            $this->query['desc'] = 'created_at';
        }
        return parent::prepareCollection(new Collection);
    }

}
