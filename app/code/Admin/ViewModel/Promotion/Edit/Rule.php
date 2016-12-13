<?php

namespace Seahinet\Admin\ViewModel\Promotion\Edit;

use Seahinet\Admin\ViewModel\Edit;
use Seahinet\Lib\Source\Store;
use Seahinet\Lib\Session\Segment;

class Rule extends Edit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('promotion/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('promotion/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Promotion Rule' : 'Add New Promotion Rule';
    }

    protected function prepareElements($columns = [])
    {
        $user = (new Segment('admin'))->get('user');
        $columns = [
            'id' => [
                'type' => 'hidden',
            ],
            'csrf' => [
                'type' => 'csrf'
            ],
            'name' => [
                'type' => 'text',
                'label' => 'Name',
                'required' => 'required'
            ],
            'description' => [
                'type' => 'textarea',
                'label' => 'Description',
                'comment' => 'Please introduce the condition of this promotion rule.'
            ],
            'store_id' => ($user->getStore() ? [
        'type' => 'hidden'
            ] : [
        'type' => 'multiselect',
        'options' => (new Store)->getSourceArray(),
        'label' => 'Store'
            ]),
            'from_date' => [
                'type' => 'datetime',
                'label' => 'From Date'
            ],
            'to_date' => [
                'type' => 'datetime',
                'label' => 'To Date'
            ],
            'use_coupon' => [
                'type' => 'select',
                'label' => 'Use Coupon',
                'options' => [
                    'No', 'Yes'
                ],
                'required' => 'required'
            ],
            'uses_per_coupon' => [
                'type' => 'tel',
                'label' => 'Uses per Coupon',
                'attrs' => [
                    'data-base' => '#use_coupon'
                ]
            ],
            'uses_per_customer' => [
                'type' => 'tel',
                'label' => 'Uses per Customer',
                'attrs' => [
                    'data-base' => '#use_coupon'
                ]
            ],
            'sort_order' => [
                'type' => 'tel',
                'label' => 'Priority',
                'required' => 'required'
            ],
            'status' => [
                'type' => 'select',
                'label' => 'Status',
                'options' => [
                    1 => 'Enabled',
                    0 => 'Disabled'
                ],
                'required' => 'required'
            ]
        ];
        return parent::prepareElements($columns);
    }

}
