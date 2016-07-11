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

    protected $editUrl = '';
    protected $deleteUrl = '';
    protected $action = ['getEditAction', 'getDeleteAction'];
    protected $translateDomain = 'review';

    public function getEditAction($item)
    {
        return '<a href="' . $this->getEditUrl() . '?id=' . $item['id'] . '"title="' . $this->translate('Edit') .
                '"><span class="fa fa-fw fa-file-text-o" aria-hidden="true"></span><span class="sr-only">'
                . $this->translate('Edit') . '</span></a>';
    }

    public function getDeleteAction($item)
    {
        return '<a href="' . $this->getDeleteUrl() . '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-fw fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    public function getEditUrl()
    {
        if ($this->editUrl === '') {
            return $this->editUrl = $this->getAdminUrl(':ADMIN/catalog_product_review/edit/');
        }
        return $this->editUrl;
    }

    public function getDeleteUrl()
    {
        if ($this->deleteUrl === '') {
            return $this->deleteUrl = $this->getAdminUrl(':ADMIN/catalog_product_review/delete/');
        }
        return $this->deleteUrl;
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
            'subject' => [
                'type' => 'text',
                'label' => 'Subject'
            ],
            'order_id' => [
                'type' => 'text',
                'label' => 'Order'
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'options' => [
                    1 => 'Disabled',
                    0 => 'Enabled'
                ]
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        if (is_null($collection)) {
            $collection = new Collection;
            $user = (new Segment('admin'))->get('user');
            if ($user->getStore()) {
                $collection->where(['store_id' => $user->getStore()->getId()]);
            }
        }
        return parent::prepareCollection($collection);
    }

}
