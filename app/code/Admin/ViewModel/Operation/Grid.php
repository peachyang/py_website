<?php

namespace Seahinet\Admin\ViewModel\Operation;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\Admin\Model\Collection\Operation as Collection;

class Grid extends PGrid
{
    protected $editUrl = '';
    protected $deleteUrl = '';
    protected $action = ['getEditAction', 'getDeleteAction'];

    public function getEditAction($item)
    {
        if($item['is_system']){
            return '';
        }
        return '<a href="' . $this->getEditUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Edit') .
                '"><span class="fa fa-file-text-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Edit') . '</span></a>';
    }

    public function getDeleteAction($item)
    {
        if($item['is_system']){
            return '';
        }
        return '<a href="' . $this->getDeleteUrl() . '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    public function getEditUrl()
    {
        if ($this->editUrl === '') {
            $this->editUrl = $this->getAdminUrl(':ADMIN/operation/edit/');
        }
        return $this->editUrl;
    }

    public function getDeleteUrl()
    {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/operation/delete/');
        }
        return $this->deleteUrl;
    }

    protected function prepareColumns()
    {
        return [
            'name' => [
                'label' => 'Name'
            ],
            'description' => [
                'label' => 'Description'
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        return parent::prepareCollection(new Collection);
    }

}