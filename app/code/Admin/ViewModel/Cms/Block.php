<?php

namespace Seahinet\Admin\ViewModel\Cms;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Cms\Model\Collection\Block as Collection;
use Seahinet\Lib\Model\AbstractCollection;

class Block extends Grid
{

    public function __construct()
    {
        $this->setVariable('title', 'Block Management');
        parent::__construct();
    }

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
                'label' => 'Code',
                'class' => 'text-left'
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

    protected function prepareCollection(AbstractCollection $collection = null)
    {
        return parent::prepareCollection(new Collection);
    }

}
