<?php

namespace Seahinet\Admin\ViewModel\Article\Grid;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\Article\Model\Collection\Product\Review as Collection;
use Seahinet\Lib\Session\Segment;
use Seahinet\Article\Source\Product;
use Seahinet\Customer\Source\Customer;
use Seahinet\Lib\Source\Language;

class Review extends PGrid
{

    protected $action = [
        'getEditAction' => 'Admin\\Article\\Product\\Review::edit',
        'getDeleteAction' => 'Admin\\Article\\Product\\Review::delete'
    ];
    protected $translateDomain = 'article_product_review';

    public function getEditAction($item)
    {
        return '<a href="' . $this->getAdminUrl(':ADMIN/article_product_review/edit/?id=') . $item['id'] . '"title="' . $this->translate('Edit') .
                '"><span class="fa fa-fw fa-file-text-o" aria-hidden="true"></span><span class="sr-only">'
                . $this->translate('Edit') . '</span></a>';
    }

    public function getDeleteAction($item)
    {
        return '<a href="' . $this->getAdminUrl(':ADMIN/article_product_review/delete/') . '" data-method="delete" data-params="id=' . $item['id'] .
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
            'article_id' => [
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
