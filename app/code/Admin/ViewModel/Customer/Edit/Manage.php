<?php

namespace Seahinet\Admin\ViewModel\Customer\Edit;

use Seahinet\Admin\ViewModel\Eav\Edit as PEdit;
use Seahinet\Customer\Source\{
    Group,
    Set
};
use Seahinet\Lib\Source\Language;
use Seahinet\Lib\Source\Store;
use Seahinet\Lib\Session\Segment;

class Manage extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('customer_manage/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('customer_manage/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Customer' : 'Add New Customer';
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
            'increment_id' => ($this->getQuery('id') ? [
        'type' => 'label',
        'label' => 'Human-Friendly ID'
            ] : [
        'type' => 'hidden'
            ]),
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
        'required' => 'required',
        'comment' => 'User Registration Source'
            ]),
            'group_id' => [
                'type' => 'multiselect',
                'label' => 'Customer Group',
                'required' => 'required',
                'options' => (new Group)->getSourceArray()
            ],
            'language_id' => [
                'type' => 'select',
                'label' => 'Language',
                'required' => 'required',
                'options' => (new Language)->getSourceArray(),
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
