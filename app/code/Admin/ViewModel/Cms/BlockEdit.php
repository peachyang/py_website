<?php

namespace Seahinet\Admin\ViewModel\Cms;

use Seahinet\Admin\ViewModel\Edit;
use Seahinet\Lib\Source\Language;

class BlockEdit extends Edit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('cms_block/save/');
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Block' : 'Add Block';
    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'id' => [
                'type' => 'hidden',
            ],
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
                'label' => 'Content'
            ]
        ];
        return parent::prepareElements($columns);
    }

}
