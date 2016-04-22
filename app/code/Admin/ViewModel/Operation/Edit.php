<?php

namespace Seahinet\Admin\ViewModel\User;

use Seahinet\Admin\ViewModel\Edit as PEdit;

class Edit extends PEdit
{

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit User' : 'Add User';
    }

    public function getSaveUrl()
    {
        return $this->getAdminUrl('user/save/');
    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'id' => [
                'type' => 'hidden'
            ],
            'username' => [
                'type' => 'text',
                'label' => 'Username',
                'required' => 'required',
                'attrs' => [
                    'spellcheck' => 'false'
                ]
            ],
            'email' => [
                'type' => 'email',
                'label' => 'EMail',
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
