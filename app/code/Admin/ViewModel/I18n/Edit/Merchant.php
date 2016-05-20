<?php

namespace Seahinet\Admin\ViewModel\I18n\Edit;

use Seahinet\Admin\ViewModel\Edit as PEdit;

class Merchant extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('i18n_merchant/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('i18n_merchant/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Merchant' : 'Add Merchant';
    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'id' => [
                'type' => 'hidden',
            ],
            'code' => [
                'type' => 'text',
                'label' => 'Name',
                'required' => 'required'
            ],
            'is_default' => [
                'type' => 'select',
                'label' => 'Is Default',
                'required' => 'required',
                'options' => ['No', 'Yes']
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