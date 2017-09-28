<?php

namespace Seahinet\Admin\ViewModel\Article\Edit;

use Seahinet\Admin\ViewModel\Eav\Edit as PEdit;
use Seahinet\Article\Source\Set;
use Seahinet\Lib\Source\Store;
use Seahinet\Lib\Session\Segment;

class Product extends PEdit
{

    protected $hasUploadingFile = true;

    public function getSaveUrl()
    {
        return $this->getAdminUrl('article_product/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('article_product/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Product' : 'Add New Product';
    }

    protected function prepareElements($columns = [])
    {
        $user = (new Segment('admin'))->get('user');
        $model = $this->getVariable('model');
        $columns = [
            'id' => [
                'type' => 'hidden'
            ],
            'csrf' => [
                'type' => 'csrf'
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
