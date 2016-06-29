<?php

namespace Seahinet\Admin\ViewModel\Api\Grid;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\Api\Model\Collection\Soap\Role as Collection;

class SoapRole extends PGrid {
    
    protected $editUrl = '';
    protected $deleteUrl = '';
    protected $action = ['getEditAction', 'getDeleteAction'];

    public function getEditAction($item) {
        return '<a href="' . $this->getEditUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Edit') .
                '"><span class="fa fa-fw fa-file-text-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Edit') . '</span></a>';
    }

    public function getDeleteAction($item) {
        return '<a href="' . $this->getDeleteUrl() . '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-fw fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    public function getEditUrl() {
        if ($this->editUrl === '') {
            $this->editUrl = $this->getAdminUrl(':ADMIN/api_soap_role/edit/');
        }
        return $this->editUrl;
    }

    public function getDeleteUrl() {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/api_soap_role/delete/');
        }
        return $this->deleteUrl;
    }

    protected function prepareColumns() {
        return [
            'name' => [
                'label' => 'Name'
            ] 
        ];
    }

    public function prepareCollection($collection = NULL) 
    {
        return parent::prepareCollection(new Collection);
    }

}
