<?php

namespace Seahinet\Admin\ViewModel\Eav;

use Seahinet\Admin\ViewModel\Edit as PEdit;
use Seahinet\Lib\Source\Eav\Attribute\Set;

class BeforeEdit extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getUri()->withQuery('');
    }

    protected function prepareElements($columns = [])
    {
        return [
            'attribute_set' => [
                'type' => 'select',
                'label' => 'Attribute Set',
                'required' => 'required',
                'options' => (new Set)->getSourceArray()
            ]
        ];
    }

}
