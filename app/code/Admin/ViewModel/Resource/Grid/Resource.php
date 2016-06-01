<?php

namespace Seahinet\Admin\ViewModel\Resource\Grid;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\Resource\Model\Collection\Resource as Collection;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\Store;
use Seahinet\Resource\Source\Category;
use Seahinet\Resource\Source\FileType;
use Seahinet\Resource\Model\Resource as Model;

class Resource extends PGrid
{

    protected $editUrl = '';
    protected $deleteUrl = '';
    protected $action = ['getDeleteAction'];
    protected $messAction = ['getMessDeleteAction'];

    public function getDeleteAction($item)
    {
        return '<a href="' . $this->getDeleteUrl() . '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-fw fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    public function getMessDeleteAction()
    {
        return '<a href="' . $this->getDeleteUrl() . '" data-method="delete" data-serialize=".grid .table" title="' . $this->translate('Delete') .
                '"><span>' . $this->translate('Delete') . '</span></a>';
    }

    public function getDeleteUrl()
    {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/resource_resource/delete/');
        }
        return $this->deleteUrl;
    }

    protected function prepareColumns()
    {
        $model = new Model;
        $user = (new Segment('admin'))->get('user');
        return [
            'store_id' => ($user->getStore() ? [
                'use4popupfilter' => false,
                'type' => 'hidden',
                'value' => $user->getStore()->getId(),
                'use4sort' => false,
                'use4filter' => false
                    ] : [
                'type' => 'select',
                'use4popupfilter' => false,
                'options' => (new Store)->getSourceArray(),
                'label' => 'Store'
                    ]),
            'category_id' => [
                'type' => 'select',
                'options' => (new Category)->getSourceArray(),
                'label' => 'Category',
                'empty_string' => '(NULL)',
                'use4popupfilter' => true
            ],
            'file_type' => [
                'label' => 'File Type',
                'type' => 'select',
                'use4popupfilter' => true,
                'options' => (new FileType)->getSourceArray()
            ],
            'uploaded_name' => [
                'label' => 'Uploaded Name',
                'use4popupfilter' => true
            ],
            'real_name' => [
                'label' => 'Real Name',
                'fileUrl' => $this->getResourceUrl(),
                'use4popupfilter' => true
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
