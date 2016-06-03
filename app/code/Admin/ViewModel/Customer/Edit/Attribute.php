<?php

namespace Seahinet\Admin\ViewModel\Customer\Edit;

use Seahinet\Admin\ViewModel\Edit as PEdit;

class Attribute extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('customer_attribute/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('customer_attribute/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Customer Attribute' : 'Add New Customer Attribute';
    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'id' => [
                'type' => 'hidden',
            ],
            'csrf' => [
                'type' => 'csrf'
            ],
            'code' => [
                'type' => 'text',
                'label' => 'Code',
                'required' => 'required'
            ],
            'type' => [
                'label' => 'Type',
                'type' => 'select',
                'required' => 'required',
                'options' => [
                    'varchar' => 'Charector',
                    'int' => 'Integer',
                    'decimal' => 'Decimal',
                    'text' => 'Text',
                    'blob' => 'Binary',
                    'datetime' => 'Date/Time'
                ]
            ],
            'input' => [
                'label' => 'Input Widget',
                'type' => 'select',
                'required' => 'required',
                'options' => [
                    'Text' => [
                        'text' => 'Text',
                        'url' => 'Url',
                        'tel' => 'Tel',
                        'number' => 'Number',
                        'email' => 'Email',
                        'color' => 'Color',
                        'password' => 'Password',
                        'textarea' => 'Textarea',
                    ],
                    'File' => [
                        'file' => 'File',
                    ],
                    'Select' => [
                        'select' => 'Dropdown',
                        'radio' => 'Radio',
                        'checkbox' => 'CheckBox',
                        'multiselect' => 'Multi-Select',
                    ],
                    'Date/Time' => [
                        'date' => 'Date',
                        'time' => 'Time',
                        'datetime' => 'Date&amp;Time'
                    ]
                ],
            ],
            'validation' => [
                'label' => 'Validation',
                'type' => 'text'
            ],
            'is_required' => [
                'label' => 'Is Required',
                'type' => 'select',
                'required' => 'required',
                'options' => [
                    1 => 'Yes',
                    0 => 'No'
                ]
            ],
            'default_value' => [
                'label' => 'Default Value',
                'type' => 'text'
            ],
            'is_unique' => [
                'label' => 'Is Unique',
                'type' => 'select',
                'required' => 'required',
                'options' => [
                    1 => 'Yes',
                    0 => 'No'
                ]
            ],
            'searchable' => [
                'label' => 'Searchable',
                'type' => 'select',
                'required' => 'required',
                'options' => [
                    1 => 'Yes',
                    0 => 'No'
                ]
            ],
            'sortable' => [
                'label' => 'Sortable',
                'type' => 'select',
                'required' => 'required',
                'options' => [
                    1 => 'Yes',
                    0 => 'No'
                ]
            ],
            'filterable' => [
                'label' => 'Filterable',
                'type' => 'select',
                'required' => 'required',
                'options' => [
                    1 => 'Yes',
                    0 => 'No'
                ]
            ],
            'comparable' => [
                'label' => 'Comparable',
                'type' => 'select',
                'required' => 'required',
                'options' => [
                    1 => 'Yes',
                    0 => 'No'
                ]
            ],
            'sort_order' => [
                'label' => 'Sort Order',
                'type' => 'tel'
            ]
        ];
        return parent::prepareElements($columns);
    }

}
