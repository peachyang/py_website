<?php

namespace Seahinet\Admin\ViewModel\Cms;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Cms\Model\Collection\Page as Collection;

class Page extends Grid
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
            $this->editUrl = $this->getAdminUrl(':ADMIN/cms_page/edit/');
        }
        return $this->editUrl;
    }

    public function getDeleteUrl()
    {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/cms_page/delete/');
        }
        return $this->deleteUrl;
    }

    protected function prepareColumns()
    {
        return [
            'id' => [
                'label' => 'ID',
            ],
            'parent_id' => [
                'label' => 'Parent ID',
            ],
            'title' => [
                'label' => 'Title',
                'class' => 'text-left'
            ],
            'uri_key' => [
                'label' => 'Uri Key',
                'class' => 'text-left',
            ],
            'language' => [
                'label' => 'Language',
                'use4sort' => false,
                'use4filter' => false
            ],
            'status' => [
                'label' => 'Status',
                'sortby' => 'cms_page:status',
                'type' => 'select',
                'options' => [
                    'Disabled',
                    'Enabled'
                ]
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        return parent::prepareCollection(new Collection);
    }

}