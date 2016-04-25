<?php

namespace Seahinet\Admin\ViewModel\Language;

use Seahinet\Admin\ViewModel\Edit as PEdit;
use Seahinet\Lib\Source\Store;

class Edit extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('language/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('language/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Language' : 'Add Language';
    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'id' => [
                'type' => 'hidden',
            ],
            'code' => [
                'type' => 'text',
                'label' => 'Code',
                'required' => 'required'
            ],
            'name' => [
                'type' => 'text',
                'label' => 'Name',
                'required' => 'required'
            ],
            'store_id' => [
                'type' => 'select',
                'label' => 'Store',
                'options' => (new Store)->getSourceArray(),
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
