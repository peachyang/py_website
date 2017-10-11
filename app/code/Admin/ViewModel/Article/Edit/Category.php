<?php

namespace Seahinet\Admin\ViewModel\Article\Edit;

use Seahinet\Admin\ViewModel\Eav\Edit as PEdit;
use Seahinet\Lib\Source\Store;
use Seahinet\Lib\Session\Segment;

class Category extends PEdit
{

    protected $hasUploadingFile = true;

    public function getSaveUrl()
    {
        return $this->getAdminUrl('article_category/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('article_category/delete/');
        }
        return false;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Category' : 'Add New Category';
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
            'parent_id' => [
                'type' => 'hidden',
                'value' => $this->getQuery('pid', '')
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
