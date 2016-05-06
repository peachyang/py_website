<?php

namespace Seahinet\Admin\ViewModel\Currency;

use Seahinet\Admin\ViewModel\Edit as PEdit;
use Seahinet\Lib\Source\Language;

class Edit extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('i18n_currency/save/');
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Currency' : 'Add Currency';
    }

    protected function prepareElements($columns = [])
    {
        $model = $this->getVariable('model');
        $columns = [
            'id' => [
                'type' => 'hidden',
            ],
            'code' => [
                'type' => 'text',
                'label' => 'Code',
                'attrs' => [
                    'disabled' => 'disabled'
                ]
            ],
            'symbol' => [
                'type' => 'text',
                'label' => 'Symbol',
                'required' => 'required'
            ],
            'rate' => [
                'type' => 'number',
                'label' => 'Currency Rate',
                'required' => 'required'
            ],
            'format' => [
                'type' => 'text',
                'label' => 'Format',
                'required' => 'required',
                'comment' => 'For detail please visit <a href="http://php.net/manual/en/function.sprintf.php">sprintf</a>'
            ]
        ];
        return parent::prepareElements($columns);
    }

}
