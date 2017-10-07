<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\Model\Collection\Eav\Type as Collection;

class Indexer extends Grid
{

    protected $action = ['getReindexAction' => 'Admin\\Indexer\\Rebuild'];
    protected $messAction = ['getMessReindexAction' => 'Admin\\Indexer\\Rebuild'];

    public function getReindexAction($item)
    {
        return '<a href="' . $this->getAdminUrl(':ADMIN/indexer/rebuild/') . '" data-method="post" data-params="id=' . $item['id'] . '" title="' . $this->translate('Rebuild') .
                '"><span class="fa fa-fw fa-refresh" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Rebuild') . '</span></a>';
    }

    public function getMessReindexAction()
    {
        return '<a href="' . $this->getAdminUrl(':ADMIN/indexer/rebuild/') . '" data-method="post" data-serialize=".grid .table" title="' . $this->translate('Rebuild') .
                '"><span>' . $this->translate('Rebuild') . '</span></a>';
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
        $collection->columns(['code']);
        $indexers = [];
        foreach ($collection as $type) {
            $indexers[] = ['code' => ucwords(str_replace('_', ' ', $type['code'])), 'id' => $type['code']];
        }
        $config = $this->getConfig();
        if (isset($config['indexer'])) {
            foreach ($config['indexer'] as $code => $info) {
                $indexers[] = ['code' => $info['title'] ?? $code, 'id' => $code];
            }
        }
        return $indexers;
    }

}
