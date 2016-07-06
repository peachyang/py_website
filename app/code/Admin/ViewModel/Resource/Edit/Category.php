<?php

namespace Seahinet\Admin\ViewModel\Resource\Edit;

use Seahinet\Admin\ViewModel\Edit as PEdit;
use Seahinet\Resource\Source\Category as CategorySource;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\Language;
use Seahinet\Lib\Source\Store;

class Category extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('resource_category/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('resource_category/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Category' : 'Add New Category';
    }

    protected function prepareElements($columns = [])
    {
        $model = $this->getVariable('model');
        $user = (new Segment('admin'))->get('user');
        $languages = (new Language)->getSourceArray();
        $columns = [
            'id' => [
                'type' => 'hidden',
            ],
            'csrf' => [
                'type' => 'csrf'
            ],
            'parent_id' => [
                'type' => 'select',
                'options' => (new CategorySource)->getSourceArray($model ? $model->getId() : []),
                'label' => 'Parent ID',
                'empty_string' => '(NULL)',
            ],
            'store_id' => ($user->getStore() ? [
                'type' => 'hidden',
                'value' => $user->getStore()->getId()
                    ] : [
                'type' => 'select',
                'options' => (new Store)->getSourceArray(),
                'label' => 'Store',
                'required' => 'required',
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
                'options' => $languages,
                'attrs' => [
                    'multiple' => 'multiple'
                ]
            ],
            'name' => [
                'type' => 'multitext',
                'label' => 'Name',
                'required' => 'required',
                'base' => '#language_id',
                'options' => $languages
            ]
        ];
        return parent::prepareElements($columns);
    }

}
