<?php

namespace Seahinet\Admin\ViewModel\Sales\Grid;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\Sales\Model\Collection\Order\Status as Collection;

class Status extends PGrid
{

    protected $editUrl = '';
    protected $deleteUrl = '';
    protected $translateDomain = 'sales';
    protected $action = ['getEditAction', 'getDeleteAction'];

    public function getEditAction($item)
    {
        return '<a href="' . $this->getEditUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Edit') .
                '"><span class="fa fa-fw fa-file-text-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Edit') . '</span></a>';
    }

    public function getDeleteAction($item)
    {
        return $item['is_default'] ? false : '<a href="' . $this->getDeleteUrl() .
                '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-fw fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    public function getEditUrl()
    {
        if ($this->editUrl === '') {
            $this->editUrl = $this->getAdminUrl(':ADMIN/sales_status/edit/');
        }
        return $this->editUrl;
    }

    public function getDeleteUrl()
    {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/sales_status/delete/');
        }
        return $this->deleteUrl;
    }

    protected function prepareColumns()
    {
        return [
            'id' => [
                'label' => 'ID',
                'use4sort' => false
            ],
            'phase' => [
                'label' => 'Phase',
                'use4sort' => false
            ],
            'name' => [
                'label' => 'Name',
                'use4sort' => false
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        $collection->join('sales_order_phase', 'sales_order_phase.id=sales_order_status.phase_id', ['phase' => 'name'])
                ->order('phase_id ASC, sales_order_status.id ASC');
        return $collection;
    }

}
