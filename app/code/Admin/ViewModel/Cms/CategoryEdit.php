<?php

namespace Seahinet\Admin\ViewModel\Cms;

use Seahinet\Admin\ViewModel\Edit;
use Seahinet\Cms\Source\Category;
use Seahinet\Lib\Source\Language;

class CategoryEdit extends Edit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('cms_category/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('cms_category/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Category' : 'Add Category';
    }

    protected function prepareElements($columns = [])
    {
        $languages = (new Language)->getSourceArray();
        $model = $this->getVariable('model');
        $columns = [
            'id' => [
                'type' => 'hidden',
            ],
            'parent_id' => [
                'type' => 'select',
                'label' => 'Parent ID',
                'empty_string' => '(NULL)',
                'options' => (new Category)->getSourceArray($model ? $model->getId() : [])
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
            'uri_key' => [
                'type' => 'text',
                'label' => 'Uri Key',
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
            ],
            'name' => [
                'type' => 'multitext',
                'label' => 'Name',
                'required' => 'required',
                'base' => '#language_id--',
                'options' => $languages
            ]
        ];
        return parent::prepareElements($columns);
    }

}
