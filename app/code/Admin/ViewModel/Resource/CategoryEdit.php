<?php

namespace Seahinet\Admin\ViewModel\Resource;

use Seahinet\Admin\ViewModel\Edit;
use Seahinet\Resource\Source\Category;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\Language;
use Seahinet\Lib\Source\Store;

class CategoryEdit extends Edit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('Resource_Category/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('Resource_Category/delete/');
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
                'value'=>(($model&&$model->getId())?(new Category())->getParentIdArray($model->getId()):[]),
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
                'value'=>(($model&&$model->getId())?(new Category())->getLanguageIdArray($model->getId()):[]),
                'options' => (new Language)->getSourceArray(),
                'attrs' => [
                    'multiple' => 'multiple'
                ]
            ],
            'name_language' => [
                'type' => 'widget',
                'label' => 'Category Name',
                'widget' => 'category_language',
                'namelanguage'=>(($model&&$model->getId())?(new Category())->getNameArray($model->getId()):[]),
                'required' => 'required'
            ]
            
        ];
        return parent::prepareElements($columns);
    }

}
