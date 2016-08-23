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
                'class' => 'htmleditor'
            ],
            'store_id' => ($user->getStore() ? [
                'type' => 'hidden',
                'value' => $user->getStore()->getId()
                    ] : [
                'type' => 'select',
                'options' => (new Store)->getSourceArray(),
                'label' => 'Store',
                'empty_string' => '(NULL)'
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
            'stop_processing' => [
                'type' => 'select',
                'label' => 'Stop Processing',
                'options' => [
                    'No', 'Yes'
                ],
                'required' => 'required'
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
