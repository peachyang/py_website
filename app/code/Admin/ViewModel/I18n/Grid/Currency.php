<?php

namespace Seahinet\Admin\ViewModel\I18n\Grid;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\I18n\Model\Collection\Currency as Collection;

class Currency extends PGrid
{

    protected $editUrl = '';
    protected $deleteUrl = '';
    protected $action = ['getEditAction', 'getSyncAction'];

    public function getEditAction($item)
    {
        return '<a href="' . $this->getEditUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Edit') .
                '"><span class="fa fa-fw fa-file-text-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Edit') . '</span></a>';
    }

    public function getSyncAction($item)
    {
        return '<a href="' . $this->getSyncUrl() . '?code=' . $item['code'] . '" title="' . $this->translate('Synchronize') .
                '"><span class="fa fa-fw fa-refresh" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Synchronize') . '</span></a>';
    }

    public function getEditUrl()
    {
        if ($this->editUrl === '') {
            $this->editUrl = $this->getAdminUrl(':ADMIN/i18n_currency/edit/');
        }
        return $this->editUrl;
    }

    public function getSyncUrl()
    {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/i18n_currency/sync/');
        }
        return $this->deleteUrl;
    }

    protected function prepareColumns()
    {
        return [
            'code' => [
                'label' => 'Code',
            ],
            'symbol' => [
                'label' => 'Symbol'
            ],
            'rate' => [
                'label' => 'Currency Rate'
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        return parent::prepareCollection($collection);
    }

}
