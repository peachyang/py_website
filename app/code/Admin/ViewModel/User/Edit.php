<?php

namespace Seahinet\Admin\ViewModel\User;

use Seahinet\Admin\Source\Role;
use Seahinet\Admin\ViewModel\Edit as PEdit;

class Edit extends PEdit
{

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit User' : 'Add New User';
    }

    public function getSaveUrl()
    {
        return $this->getAdminUrl('user/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('user/delete/');
        }
        return false;
    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'id' => [
                'type' => 'hidden'
            ],
            'csrf' => [
                'type' => 'csrf'
            ],
            'username' => [
                'type' => 'text',
                'label' => 'Username',
                'required' => 'required',
                'attrs' => [
                    'spellcheck' => 'false'
                ]
            ],
            'role_id' => [
                'type' => 'select',
                'label' => 'Role',
                'options' => (new Role)->getSourceArray(true),
                'required' => 'required'
            ],
            'email' => [
                'type' => 'email',
                'label' => 'Email',
                'required' => 'required',
                'class' => 'email'
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
            'password' => [
                'type' => 'password',
                'label' => 'Password',
                'value' => '',
                'required' => 'required',
                'attrs' => [
                    'minlength' => 6,
                    'autocomplete' => 'off'
                ]
            ],
            'cpassword' => [
                'type' => 'password',
                'label' => 'Confirm Password',
                'value' => '',
                'required' => 'required',
                'attrs' => [
                    'minlength' => 6,
                    'data-rule-equalto' => '#password',
                    'autocomplete' => 'off'
                ]
            ],
        ];
        return parent::prepareElements($columns);
    }
}
