<?php

namespace Seahinet\Admin\ViewModel\I18n\Edit;

use Seahinet\Admin\ViewModel\Edit as PEdit;
use Seahinet\Lib\Source\Merchant;

class Store extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('i18n_store/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('i18n_store/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Store' : 'Add Store';
    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'id' => [
                'type' => 'hidden',
            ],
            'csrf' => [
                'type' => 'csrf'
            ],
            'code' => [
                'type' => 'text',
                'label' => 'Name',
                'required' => 'required'
            ],
            'merchant_id' => [
                'type' => 'select',
                'label' => 'Merchant',
                'options' => (new Merchant)->getSourceArray(),
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
