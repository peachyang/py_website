<?php

namespace Seahinet\Admin\ViewModel\Promotion\Grid;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Lib\Session\Segment;
use Seahinet\Promotion\Model\Collection\Rule as Collection;

class Rule extends Grid
{

    protected $editUrl = '';
    protected $deleteUrl = '';
    protected $enableUrl = '';
    protected $disableUrl = '';
    protected $translateDomain = 'promotion';
    protected $action = ['getEditAction', 'getDeleteAction', 'getEnableAction', 'getDisableAction'];

    public function getEditAction($item)
    {
        return '<a href="' . $this->getEditUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Edit') .
                '"><span class="fa fa-fw fa-file-text-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Edit') . '</span></a>';
    }

    public function getDeleteAction($item)
    {
        return '<a href="' . $this->getDeleteUrl() . '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-fw fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    public function getEnableAction($item)
    {
        return $item['status'] ? false : ('<a href="' . $this->getEnableUrl() . '?id='. $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Enable') .
                '"><span class="fa fa-fw fa-play" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Enable') . '</span></a>');
    }

    public function getDisableAction($item)
    {
        return $item['status'] ? ('<a href="' . $this->getDisableUrl() . '?id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Disable') .
                '"><span class="fa fa-fw fa-pause" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Disable') . '</span></a>') : false;
    }

    public function getEditUrl()
    {
        if ($this->editUrl === '') {
            $this->editUrl = $this->getAdminUrl(':ADMIN/promotion/edit/');
        }
        return $this->editUrl;
    }

    public function getDeleteUrl()
    {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/promotion/delete/');
        }
        return $this->deleteUrl;
    }

    public function getEnableUrl()
    {
        if ($this->enableUrl === '') {
            $this->enableUrl = $this->getAdminUrl(':ADMIN/promotion/enable/');
        }
        return $this->enableUrl;
    }

    public function getDisableUrl()
    {
        if ($this->disableUrl === '') {
            $this->disableUrl = $this->getAdminUrl(':ADMIN/promotion/disable/');
        }
        return $this->disableUrl;
    }

    protected function prepareColumns()
    {
        return [
            'id' => [
                'label' => 'ID',
            ],
            'name' => [
                'label' => 'Name',
                'class' => 'text-left'
            ],
            'use_coupon' =>[
                'label' => 'Use Coupon',
                'type' => 'select',
                'options' => [
                    'No',
                    'Yes'
                ]
            ],
            'from_date' => [
                'type' => 'datetime',
                'label' => 'From Date'
            ],
            'to_date' => [
                'type' => 'datetime',
                'label' => 'To Date'
            ],
            'sort_order' => [
                'type' => 'tel',
                'label' => 'Priority'
            ],
            'status' => [
                'label' => 'Status',
                'sortby' => 'cms_block:status',
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
        $user = (new Segment('admin'))->get('user');
        $collection = new Collection;
        if ($user->getStore()) {
            $collection->where(['store_id' => $user->getStore()->getId()]);
        }
        if (!$this->getQuery('desc')) {
            $this->query['desc'] = 'created_at';
        }
        return parent::prepareCollection($collection);
    }

}
