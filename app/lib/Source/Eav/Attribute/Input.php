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
        ];
    }

}
