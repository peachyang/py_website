<?php

namespace Seahinet\Admin\ViewModel\Resource;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Resource\Model\Collection\Category as Collection;
use Seahinet\Lib\Session\Segment;
use Seahinet\Resource\Source\Category as CategorySource;
use Seahinet\Lib\Source\Store;
use Seahinet\Lib\Source\Language;

class Category extends Grid
{

    protected $editUrl = '';
    protected $deleteUrl = '';
    protected $action = ['getEditAction', 'getDeleteAction'];

    public function getEditAction($item)
    {
        return '<a href="' . $this->getEditUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Edit') .
                '"><span class="fa fa-file-text-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Edit') . '</span></a>';
    }

    public function getDeleteAction($item)
    {
        return '<a href="' . $this->getDeleteUrl() . '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    public function getEditUrl()
    {
        if ($this->editUrl === '') {
            $this->editUrl = $this->getAdminUrl(':ADMIN/Resource_Category/edit/');
        }
        return $this->editUrl;
    }

    public function getDeleteUrl()
    {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/Resource_Category/delete/');
        }
        return $this->deleteUrl;
    }

    protected function prepareColumns()
    {
        
        $model = $this->getVariable('model');
        $user = (new Segment('admin'))->get('user');
        return [
            'id' => [
                'label' => 'ID',
                'use4filter' => false
            ],
            'store_id' => ($user->getStore() ? [
                'type' => 'hidden',
                'value' => $user->getStore()->getId()
            ] : [
                'type' => 'select',
                'options' => (new Store)->getSourceArray(),
                'label' => 'Store',
                'required' => 'required',
            ]),
            'code' => [
                'type' => 'text',
                'label' => 'Code',
                'required' => 'required'
            ],
            'parent_id' => [
                'label' => 'Parent',
                'type' => 'select',
                'options' => (new CategorySource())->getSourceArray($model ? $model->getId() : [])
            ],
            'name' => [
                'label' => 'Category name',
                'class' => 'text-left'
            ],
            'code' => [
                'label' => 'Code',
                'class' => 'text-left',
                'use4sort' => false,
                'use4filter' => false
            ],
            'language' => [
                'label' => 'Language',
                'use4sort' => false,
                'use4filter' => false
            ]
        ];
    }
    
    protected function prepareCollection($collection = null)
    {
        $user = (new Segment('admin'))->get('user');
        $collection = new Collection;
        if ($user->getStore()) {
            $collection->where(['store_id' => $user->getStore()->getId()]);
        }
        return parent::prepareCollection($collection);
    }

}
