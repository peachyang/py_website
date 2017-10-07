<?php

namespace Seahinet\Lib\Source\Eav\Attribute;

use Seahinet\Lib\Source\SourceInterface;

class Input implements SourceInterface
{

    public function getSourceArray()
    {
        return [
            'Text' => [
                'text' => 'Text',
                'url' => 'Url',
                'tel' => 'Tel',
                'number' => 'Number',
                'email' => 'Email',
                'color' => 'Color',
                'password' => 'Password',
                'textarea' => 'Textarea',
                'hidden' => 'Hidden'
            ],
            'File' => [
                'file' => 'File',
            ],
            'Select' => [
                'select' => 'Dropdown',
                'radio' => 'Radio',
                'checkbox' => 'CheckBox',
                'multiselect' => 'Multi-Select',
                'bool' => 'Yes/No'
            ],
            'Date/Time' => [
                'date' => 'Date',
                'daterange' => 'Date Range',
                'time' => 'Time',
                'datetime' => 'Date&amp;Time'
            ]
        ];
    }

}
