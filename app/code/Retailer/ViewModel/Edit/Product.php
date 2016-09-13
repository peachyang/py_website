<?php

namespace Seahinet\Retailer\ViewModel\Edit;

use Seahinet\Admin\ViewModel\Eav\Edit as PEdit;
use Seahinet\Catalog\Model\Product as Model;
use Seahinet\Lib\Session\Segment;
use Seahinet\Catalog\Source\Set;
use Seahinet\Admin\ViewModel\Catalog\Edit\Product as Pview;

class Product extends PEdit
{
    public function __construct()
    {
        $this->setTemplate('retailer/productEdit');
    }
    
    public function getSaveUrl()
    {
        return $this->getBaseUrl('retailer/goods/save');
    }
    
    protected function prepareElements($columns = [])
    {
        $user = (new Segment('customer'))->get('customer');
        $model = new Model;
        //$model = $this->getVariable('model');
        $columns = [
            'id' => [
                'type' => 'hidden'
            ],
            'csrf' => [
                'type' => 'csrf'
            ],
            'product_type_id' => [
                'type' => 'hidden',
                'value' => $this->getQuery('product_type', $model['product_type_id']),
            ],
            'attribute_set_id' => [
                'type' => 'select',
                'label' => 'Attribute Set',
                'required' => 'required',
                'options' => (new Set)->getSourceArray(),
                'value' => $this->getQuery('attribute_set', $model['attribute_set_id']),
                'attrs' => [
                    'onchange' => 'location.href=\'' . $this->getUri()->withQuery(http_build_query($query = array_diff_key($this->getQuery(), ['attribute_set' => '']))) . (empty($query) ? '?' : '&') . 'attribute_set=\'+this.value;'
                ]
            ],
            'store_id' => ($user->getStore() ? [
                'type' => 'hidden',
                'value' => $user->getStore()->getId()
                    ] : [
                'type' => 'select',
                'options' => (new Store)->getSourceArray(),
                'label' => 'Store',
                'required' => 'required'
                    ]),
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