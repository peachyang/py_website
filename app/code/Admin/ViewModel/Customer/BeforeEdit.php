<?php

namespace Seahinet\Admin\ViewModel\Customer;

use Seahinet\Admin\ViewModel\Eav\BeforeEdit as PBeforeEdit;
use Seahinet\Customer\Source\Set;

class BeforeEdit extends PBeforeEdit
{

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
