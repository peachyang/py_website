<?php

namespace Seahinet\Admin\ViewModel\Resources;

use Seahinet\Admin\ViewModel\Edit;
use Seahinet\Resources\Source\Category;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\Language;
use Seahinet\Lib\Source\Store;

class CategoryEdit extends Edit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('resources_Category/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('resources_Category/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Category' : 'Add Category';
    }

    protected function prepareElements($columns = [])
    {
        $model = $this->getVariable('model');
        $user = (new Segment('admin'))->get('user');
        $columns = [
            'id' => [
                'type' => 'hidden',
            ],
            'parent_id' => [
                'type' => 'select',
                'options' => (new Category())->getSourceArray($model ? $model->getId() : []),
                'label' => 'Parent ID',
                'empty_string' => '(Top category)'
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
            'code' => [
                'type' => 'text',
                'label' => 'Code',
                'required' => 'required'
            ],
            'language_id[]' => [
                'type' => 'select',
                'label' => 'Language',
                'required' => 'required',
                'options' => (new Language)->getSourceArray(),
                'attrs' => [
                    'multiple' => 'multiple'
                ]
            ]
            
        ];
        return parent::prepareElements($columns);
    }

}
