<?php

namespace Seahinet\Admin\ViewModel\Language;

use Seahinet\Admin\ViewModel\Edit as PEdit;

class Merchant extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('language/saveMerchant/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('language/deleteMerchant/');
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
