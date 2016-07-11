<?php

namespace Seahinet\Admin\ViewModel\Catalog\Edit;

use Seahinet\Admin\ViewModel\Edit as PEdit;
use Seahinet\Catalog\Source\Product;
use Seahinet\Customer\Source\Customer;
use Seahinet\Lib\Source\Language;
use Seahinet\Lib\Session\Segment;
class Review extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('catalog_product_review/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('catalog_product_review/delete/');
        }
        return FALSE;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Review' : 'Add New Review';

    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'id' => [
                'label' => 'ID',
                'type' => 'hidden'
            ],
            'csrf' => [
                'type' => 'csrf'
            ],
            'product_id' => [
                'type' => 'select',
                'label' => 'Product',
                'required' => 'required',
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
            ]
        ];
        return parent::prepareElements($columns);
    }

}
