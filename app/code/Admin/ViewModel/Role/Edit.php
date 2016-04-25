<?php

namespace Seahinet\Admin\ViewModel\Role;

use Seahinet\Admin\ViewModel\Edit as PEdit;

class Edit extends PEdit
{

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Role' : 'Add Role';
    }

    public function getSaveUrl()
    {
        return $this->getAdminUrl('role/save/');
    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'id' => [
                'type' => 'hidden'
            ],
            'name' => [
                'type' => 'text',
                'label' => 'Name',
                'required' => 'required',
                'attrs' => [
                    'spellcheck' => 'false'
                ]
            ],
            'status' => [
                'type' => 'select',
                'label' => 'Status',
                'options' => [
                    1 => 'Enabled',
                    0 => 'Disabled'
                ],
                'required' => 'required'
            ],
            'crpassword' => [
                'type' => 'password',
                'label' => 'Current Password',
                'value' => '',
                'required' => 'required',
                'attrs' => [
                    'minlength' => 6,
                    'autocomplete' => 'off'
                ]
            ],
        ];
        return parent::prepareElements($columns);
    }
}
