<?php

namespace Seahinet\Admin\ViewModel\Operation;

use Seahinet\Admin\ViewModel\Edit as PEdit;

class Edit extends PEdit
{

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Operation' : 'Add Operation';
    }

    public function getSaveUrl()
    {
        return $this->getAdminUrl('operation/save/');
    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'id' => [
                'type' => 'hidden'
            ],
            'is_system' => [
                'type' => 'hidden',
                'value' => 0
            ],
            'name' => [
                'type' => 'text',
                'label' => 'Name',
                'required' => 'required',
                'attrs' => [
                    'spellcheck' => 'false'
                ]
            ],
            'description' => [
                'type' => 'text',
                'label' => 'Description'
            ]
        ];
        return parent::prepareElements($columns);
    }
}
