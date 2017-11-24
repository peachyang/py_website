<?php

namespace Seahinet\Admin\ViewModel\Article\Edit\Attribute;

use Seahinet\Admin\ViewModel\Edit;
use Seahinet\Lib\Source\Language;

class Set extends Edit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('article_attribute_set/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('article_attribute_set/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Product Attribute Set' : 'Add New Product Attribute Set';
    }

    protected function prepareElements($columns = [])
    {
        $languages = (new Language)->getSourceArray();
        $columns = [
            'id' => [
                'type' => 'hidden',
            ],
            'csrf' => [
                'type' => 'csrf'
            ],
            'label' => [
                'type' => 'text',
                'label' => 'Label',
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
            ],
//            'code' => [
//                'type' => 'text',
//                'label' => 'Code',
//                'required' => 'required'
//            ],
            'apply' => [
                'type' => 'widget',
                'label' => 'Attributes',
                'widget' => 'apply'
            ]
        ];
        return parent::prepareElements($columns);
    }

}
