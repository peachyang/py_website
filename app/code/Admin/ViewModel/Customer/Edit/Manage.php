<?php

namespace Seahinet\Admin\ViewModel\Customer\Edit;

use Seahinet\Admin\ViewModel\Eav\Edit as PEdit;
use Seahinet\Lib\Source\Language;
use Seahinet\Lib\Source\Store;
use Seahinet\Lib\Session\Segment;

class Manage extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('customer_manage/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('customer_manage/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Customer' : 'Add New Customer';
    }

    protected function prepareElements($columns = [])
    {
        $user = (new Segment('admin'))->get('user');
        $columns = [
            'id' => [
                'type' => 'hidden'
            ],
            'csrf' => [
                'type' => 'csrf'
            ],
            'increment_id' => ($this->getQuery('id') ? [
                'type' => 'label',
                'label' => 'Human-Friendly ID'
                    ] : [
                'type' => 'hidden'
                    ]),
            'store_id' => ($user->getStore() ? [
                'type' => 'hidden',
                'value' => $user->getStore()->getId()
                    ] : [
                'type' => 'select',
                'options' => (new Store)->getSourceArray(),
                'label' => 'Store',
                'required' => 'required'
                    ]),
            'language_id' => [
                'type' => 'select',
                'label' => 'Language',
                'required' => 'required',
                'options' => (new Language)->getSourceArray(),
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
