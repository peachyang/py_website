<?php

namespace Seahinet\Admin\ViewModel\Cms\Edit;

use Seahinet\Admin\ViewModel\Edit as PEdit;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\Language;
use Seahinet\Lib\Source\Store;

class Block extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('cms_block/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('cms_block/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Block' : 'Add Block';
    }

    protected function prepareElements($columns = [])
    {
        $user = (new Segment('admin'))->get('user');
        $columns = [
            'id' => [
                'type' => 'hidden',
            ],
            'code' => [
                'type' => 'text',
                'label' => 'Code',
                'required' => 'required'
            ],
            'store_id' => ($user->getStore() ? [
                'type' => 'hidden',
                'value' => $user->getStore()->getId()
                    ] : [
                'type' => 'select',
                'options' => (new Store)->getSourceArray(),
                'label' => 'Store',
                'empty_string' => '(NULL)'
                    ]),
            'language_id[]' => [
                'type' => 'select',
                'label' => 'Language',
                'required' => 'required',
                'options' => (new Language)->getSourceArray(),
                'attrs' => [
                    'multiple' => 'multiple'
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
            'content' => [
                'type' => 'textarea',
                'label' => 'Content',
                'class'=>'htmleditor fullbar'
            ]
        ];
        return parent::prepareElements($columns);
    }

}
