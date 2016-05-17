<?php

namespace Seahinet\Admin\ViewModel\Resource;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Resource\Model\Collection\Resource as Collection;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\Language;
use Seahinet\Lib\Source\Store;
use Seahinet\Resource\Source\Category;

class Resource extends Grid
{

    protected $editUrl = '';
    protected $deleteUrl = '';
    protected $action = ['getDeleteAction'];

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
            $this->editUrl = $this->getAdminUrl(':ADMIN/Resource_Resource/edit/');
        }
        return $this->editUrl;
    }

    public function getDeleteUrl()
    {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/Resource_Resource/delete/');
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
                'value' => $user->getStore()->getId(),
                'use4sort' => false,
                'use4filter' => false
                    ] : [
                'type' => 'select',
                'options' => (new Store)->getSourceArray(),
                'label' => 'Store'
                    ]),
            'category_id' => [
                'type' => 'select',
                'value' => (($model && $model->getId()) ? (new Category())->getParentIdArray($model->getId()) : []),
                'options' => (new Category())->getSourceArray($model ? $model->getId() : []),
                'label' => 'Category',
                'empty_string' => '(Top category)'
            ],
            'file_type' => [
                'label' => 'File Type',
                'class' => 'text-left',
                'sortby' => 'resource:file_type',
                'type' => 'select',
                'options' => [
                    'others',
                    'images',
                    'video',
                    'pdf',
                    'zip'
                ]
            ],
            'old_name' => [
                'label' => 'Old Name',
                'class' => 'text-left',
            ],
            'file_name' => [
                'label' => 'File name',
                'class' => 'text-left',
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
