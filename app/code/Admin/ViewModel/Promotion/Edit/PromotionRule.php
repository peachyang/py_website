<?php

namespace Seahinet\Admin\ViewModel\Promotion\Edit;

use Seahinet\Admin\ViewModel\Edit;
use Seahinet\Lib\Source\Store;
use Seahinet\Lib\Session\Segment;

class PromotionRule extends Edit
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
        return $this->getQuery('id') ? 'Edit Promotion Activities' : 'Add New Promotion Activities';
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
                'label' => 'Label',
                'required' => 'required',
                'comment' => 'Set promotional identification label.'
            ],
            'description' => [
                'type' => 'textarea',
                'label' => 'Condition',
                'required' => 'required',
                'comment' => 'Used to describe current conditions and the scope of the use of promotion, such as: full 100 minus 30, clothing products available.'
            ],
            'store_id' => ($user->getStore() ? [
        'type' => 'hidden'
            ] : [
        'type' => 'checkbox',
        'options' => (new Store)->getSourceArray(),
        'label' => 'AvailableIn',
        'comment' => 'Please select the range to use promotion.'
            ]),
            'from_date' => [
                'type' => 'date',
                'label' => 'From Date',
                'comment' => 'You said that the current start time.'
            ],
            'to_date' => [
                'type' => 'date',
                'label' => 'To Date'
            ],
            'use_coupon' => [
                'type' => 'hidden',
                'required' => 'required',
                'value' => 0
            ],
            'sort_order' => [
                'type' => 'tel',
                'label' => 'Priority',
                'required' => 'required',
                'comment' => 'Set priority associated with promotion,smaller the number,higher the priority.'
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
