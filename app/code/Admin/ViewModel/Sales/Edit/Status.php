<?php

namespace Seahinet\Admin\ViewModel\Sales\Edit;

use Seahinet\Admin\ViewModel\Edit as PEdit;
use Seahinet\Sales\Source\Order\Phase;

class Status extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('sales_status/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('sales_status/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit  Status' : 'Add New Status';
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
            'phase_id' => [
                'type' => 'select',
                'label' => 'Phase',
                'required' => 'required',
                'options' => (new Phase)->getSourceArray()
            ],
            'name' => [
                'type' => 'text',
                'required' => 'required',
                'label' => 'Name'
            ],
            'is_default' => [
                'type' => 'select',
                'label' => 'Is Default',
                'required' => 'required',
                'options' => [
                    1 => 'Yes',
                    0 => 'No'
                ]
            ]
        ];
        return parent::prepareElements($columns);
    }

}
