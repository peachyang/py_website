<?php

namespace Seahinet\Admin\ViewModel\Promotion\Edit;

use Seahinet\Admin\ViewModel\Edit;

class Action extends Edit
{

    public function getOptions($source)
    {
        if (is_subclass_of($source, '\\Seahinet\\Lib\\Source\\SourceInterface')) {
            return (new $source)->getSourceArray();
        }
        return [];
    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'apply_to' => [
                'type' => 'select',
                'label' => 'Apply to',
                'required' => 'required',
                'options' => ['Whole Cart', 'Item(s) Only']
            ],
            'is_fixed' => [
                'type' => 'select',
                'label' => 'Price Type',
                'required' => 'required',
                'options' => [1 => 'Fixed', 0 => 'Percent']
            ],
            'price' => [
                'type' => 'price',
                'label' => 'Price',
                'required' => 'required'
            ],
            'qty' => [
                'type' => 'number',
                'label' => 'Maximum Qty Discount is Applied To'
            ],
            'free_shipping' => [
                'type' => 'select',
                'label' => 'Free Shipping',
                'required' => 'required',
                'options' => ['No', 'Yes']
            ],
            'stop_processing' => [
                'type' => 'select',
                'label' => 'Stop Processing More Rules',
                'required' => 'required',
                'options' => ['No', 'Yes']
            ]
        ];
        return parent::prepareElements($columns);
    }

}
