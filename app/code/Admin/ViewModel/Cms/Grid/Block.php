<?php

namespace Seahinet\Admin\ViewModel\Cms\Grid;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\Cms\Model\Collection\Block as Collection;
use Seahinet\Lib\Session\Segment;

class Block extends PGrid
{

    protected $translateDomain = 'cms';
    protected $action = [
        'getEditAction' => 'Admin\\Cms\\Block::edit',
        'getDeleteAction' => 'Admin\\Cms\\Block::delete'
    ];

    public function getEditAction($item)
    {
        return '<a href="' . $this->getAdminUrl(':ADMIN/cms_block/edit/?id=') . $item['id'] . '" title="' . $this->translate('Edit') .
                '"><span class="fa fa-fw fa-file-text-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Edit') . '</span></a>';
    }

    public function getDeleteAction($item)
    {
        return '<a href="' . $this->getAdminUrl(':ADMIN/cms_block/delete/') . '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-fw fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    protected function prepareColumns()
    {
        return [
            'id' => [
                'label' => 'ID',
            ],
            'code' => [
                'label' => 'Identifier',
                'class' => 'text-left'
            ],
            'language' => [
                'label' => 'Language',
                'use4sort' => false,
                'use4filter' => false
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
