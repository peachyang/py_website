<?php

namespace Seahinet\Admin\ViewModel\I18n\Edit;

use Seahinet\Admin\ViewModel\Edit as PEdit;
use Seahinet\Lib\Source\Merchant;
use Seahinet\I18n\Source\Locale;

class Language extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('i18n_language/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('i18n_language/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Language' : 'Add New Language';
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
                'type' => 'select',
                'label' => 'Code',
                'required' => 'required',
                'options' => (new Locale)->getSourceArray()
            ],
            'name' => [
                'type' => 'text',
                'label' => 'Name'
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
