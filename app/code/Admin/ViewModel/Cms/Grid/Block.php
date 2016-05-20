<?php

namespace Seahinet\Admin\ViewModel\Cms\Grid;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\Cms\Model\Collection\Block as Collection;
use Seahinet\Lib\Session\Segment;

class Block extends PGrid
{

    protected $translateDomain = 'cms';

    public function getEditUrl($id = null)
    {
        return $this->getAdminUrl(':ADMIN/cms_block/edit/' . (is_null($id) ? '' : '?id=' . $id));
    }

    public function getDeleteUrl()
    {
        return $this->getAdminUrl(':ADMIN/cms_block/delete/');
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
        return parent::prepareCollection($collection);
    }

}