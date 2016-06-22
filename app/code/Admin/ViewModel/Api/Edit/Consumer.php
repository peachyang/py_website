<?php

namespace Seahinet\Admin\ViewModel\Api\Edit;

use Seahinet\Admin\ViewModel\Edit as PEdit;
use Seahinet\Oauth\Model\Collection\Consumer as Collection;
use Zend\Math\Rand;

class Consumer extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('api_oauth_consumer/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('api_oauth_consumer/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Consumer' : 'Add New Consumer';
    }

    protected function prepareElements($columns = [])
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            $key = $model['key'];
            $secret = $model['secret'];
        } else {
            $collection = new Collection;
            do {
                $key = Rand::getString(32, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
                $collection->reset('where')->where(['key' => $key]);
            } while (count($collection));
            do {
                $secret = Rand::getString(32, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
                $collection->reset('where')->where(['secret' => $secret]);
            } while (count($collection));
        }
        $columns = [
            'id' => [
                'type' => 'hidden',
            ],
            'csrf' => [
                'type' => 'csrf'
            ],
            'name' => [
                'type' => 'text',
                'label' => 'Name',
                'required' => 'required'
            ],
            'key' => [
                'type' => 'text',
                'label' => 'Username',
                'required' => 'required',
                'attr' => [
                    'readonly' => 'readonly'
                ],
                'value' => $key
            ],
            'secret' => [
                'type' => 'text',
                'label' => 'Password',
                'required' => 'required',
                'attr' => [
                    'readonly' => 'readonly'
                ],
                'value' => $secret
            ],
            'callback_url' => [
                'type' => 'url',
                'label' => 'Callback Url',
                'required' => 'required'
            ],
            'rejected_callback_url' => [
                'type' => 'url',
                'label' => 'Rejected Callback Url'
            ]
        ];
        return parent::prepareElements($columns);
    }

}
