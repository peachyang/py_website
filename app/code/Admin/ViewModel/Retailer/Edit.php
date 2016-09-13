<?php

namespace Seahinet\Admin\ViewModel\Retailer;

use Seahinet\Admin\ViewModel\Edit as PEdit;

class Edit extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('retailer_apply/save/');
    }

    public function getTitle()
    {
        return 'Edit Application';
    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'csrf' => [
                'type' => 'csrf'
            ],
            'customer_id' => [
                'type' => 'tel',
                'required' => 'required',
                'label' => 'Customer ID',
                'attrs' => [
                    'readonly' => 'readonly'
                ]
            ],
            'lisence_1' => [
                'type' => 'image',
                'label' => 'ID Card 1',
            ],
            'lisence_2' => [
                'type' => 'image',
                'label' => 'ID Card 2',
            ],
            'phone' => [
                'type' => 'tel',
                'required' => 'required',
                'label' => 'Phone Number',
                'attrs' => [
                    'disabled' => 'disabled'
                ]
            ],
            'bread_type' => [
                'type' => 'select',
                'required' => 'required',
                'label' => 'Brand',
                'attrs' => [
                    'disabled' => 'disabled'
                ],
                'options' => [
                    'Agency', 'Own'
                ]
            ],
            'product_type' => [
                'type' => 'text',
                'required' => 'required',
                'label' => 'Product Type',
                'attrs' => [
                    'disabled' => 'disabled'
                ]
            ],
            'status' => [
                'type' => 'select',
                'label' => 'Status',
                'options' => [
                    1 => 'Enabled',
                    0 => 'Disabled'
                ],
                'required' => 'required'
            ]
        ];
        return parent::prepareElements($columns);
    }

}
