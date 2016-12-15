<?php

namespace Seahinet\Admin\ViewModel\Api\Edit;

use Seahinet\Admin\ViewModel\Edit as PEdit;

class SoapUser extends PEdit
{

    public function getSaveUrl()
    {

        return $this->getAdminUrl('api_soap_user/save/');
    }

    public function getDeleteUrl()
    {

        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('api_soap_user/delete/');
        }
        return FALSE;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit SOAP User' : 'Add New SOAP User';
    }

    public function prepareElements($columns = [])
    {
        $columns = [
            'id' => [
                'type' => 'hidden'
            ],
            'csrf' => [
                'type' => 'csrf'
            ],
            'name' => [
                'type' => 'text',
                'label' => 'Name',
                'required' => 'required'
            ],
            'role_id' => [
                'type' => 'select',
                'label' => 'Role',
                'options' => '1'
            //'required' => 'required'
            ],
            'key' => [
                'type' => 'text',
                'label' => 'Keywords',
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
