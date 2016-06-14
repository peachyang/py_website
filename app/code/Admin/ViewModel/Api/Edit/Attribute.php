<?php

namespace Seahinet\Admin\ViewModel\Api\Edit;

use Seahinet\Admin\ViewModel\Edit as PEdit;

class Attribute extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('api_rest_attribute/save/');
    }

    public function getTitle()
    {
        return 'Edit Attribute Rules';
    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'csrf' => [
                'type' => 'csrf'
            ],
            'role_id' => [
                'type' => 'hidden',
                'value' => $this->getQuery('id')
            ],
            'attribute_id[]' => [
                'type' => 'widget',
                'label' => 'Attribute Rules',
                'required' => 'required',
                'widget' => 'attribute'
            ]
        ];
        return parent::prepareElements($columns);
    }

}
