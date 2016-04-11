<?php

namespace Seahinet\Admin\ViewModel\CMS;

use Seahinet\Admin\ViewModel\Edit;
use Seahinet\CMS\Source\Page;
use Seahinet\Lib\Source\Language;

class PageEdit extends Edit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('cms_page/save/');
    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'id' => [
                'type' => 'hidden',
            ],
            'parent_id' => [
                'type' => 'select',
                'options' => (new Page)->getSourceArray($this->getVariable('model')->getId()),
                'label' => 'Parent ID'
            ],
            'title' => [
                'type' => 'text',
                'label' => 'Title',
                'required' => 'required'
            ],
            'language_id' => [
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
                'label' => 'Url Key',
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
            'content' => [
                'type' => 'textarea',
                'label' => 'Content'
            ]
        ];
        return parent::prepareElements($columns);
    }

}
