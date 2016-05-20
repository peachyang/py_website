<?php

namespace Seahinet\Admin\ViewModel\Cms\Edit;

use Seahinet\Admin\ViewModel\Edit as PEdit;
use Seahinet\Cms\Source\Category;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\Language;
use Seahinet\Lib\Source\Store;

class Page extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('cms_page/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('cms_page/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Page' : 'Add Page';
    }

    protected function prepareElements($columns = [])
    {
        $model = $this->getVariable('model');
        $user = (new Segment('admin'))->get('user');
  
        $columns = [
            'id' => [
                'type' => 'hidden',
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
            'title' => [
                'type' => 'text',
                'label' => 'Title',
                'required' => 'required'
            ],
            'category_id[]' => [
                'type' => 'select',
                'options' => (new Category)->getSourceArray(),
                'label' => 'Category',
                'attrs' => [
                    'multiple' => 'multiple'
                ]
            ],
            'language_id[]' => [
                'type' => 'select',
                'label' => 'Language',
                'required' => 'required',
                'options' => (new Language)->getSourceArray(),
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
            'keywords' => [
                'type' => 'text',
                'label' => 'Meta Keywords'
            ],
            'description' => [
                'type' => 'text',
                'label' => 'Meta Description'
            ],
            'pageimage' => [
                'type' => 'widget',
                'label' => 'Images',
                'widget' => 'pageimage'
            ],
            'content' => [
                'type' => 'textarea',
                'label' => 'Content',
                'class' => 'htmleditor fullbar'
            ]
        ];
        return parent::prepareElements($columns);
    }

}
