<?php

namespace Seahinet\Admin\ViewModel\Article;

use Seahinet\Admin\ViewModel\Eav\BeforeEdit as PBeforeEdit;
use Seahinet\Article\Source\Set;

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
