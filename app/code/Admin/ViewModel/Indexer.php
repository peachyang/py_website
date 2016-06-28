<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\Model\Collection\Eav\Type as Collection;

class Indexer extends Grid
{

    protected $rebuildUrl = '';
    protected $action = ['getReindexAction'];
    protected $messAction = ['getMessReindexAction'];

    public function getReindexAction($item)
    {
        return '<a href="' . $this->getRebuildUrl() . '?id=' . $item['code'] . '" title="' . $this->translate('Rebuild') .
                '"><span class="fa fa-fw fa-refresh" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Rebuild') . '</span></a>';
    }

    public function getMessReindexAction()
    {
        return '<a href="' . $this->getRebuildUrl() . '" data-method="delete" data-serialize=".grid .table" title="' . $this->translate('Rebuild') .
                '"><span>' . $this->translate('Rebuild') . '</span></a>';
    }

    public function getRebuildUrl()
    {
        if ($this->rebuildUrl === '') {
            $this->rebuildUrl = $this->getAdminUrl(':ADMIN/indexer/rebuild/');
        }
        return $this->rebuildUrl;
    }

    protected function prepareColumns()
    {
        return [
            'code' => [
                'label' => 'Code',
                'use4filter' => false
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        return $collection;
    }

}
