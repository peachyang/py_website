<?php

namespace Seahinet\Admin\ViewModel\Cms;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Cms\Model\Collection\Page as Collection;
use Seahinet\Lib\Model\AbstractCollection;

class Page extends Grid
{

    public function __construct()
    {
        $this->setVariable('title', 'Page Management');
        parent::__construct();
    }

    public function getEditUrl($id = null)
    {
        return $this->getAdminUrl(':ADMIN/cms_page/edit/' . (is_null($id) ? '' : '?id=' . $id));
    }

    public function getDeleteUrl()
    {
        return $this->getAdminUrl(':ADMIN/cms_page/delete/');
    }

    protected function prepareColumns()
    {
        return [
            'id' => [
                'label' => 'ID',
            ],
            'parent_id' => [
                'label' => 'Parent ID',
            ],
            'title' => [
                'label' => 'Title',
                'class' => 'text-left'
            ],
            'uri_key' => [
                'label' => 'Uri key',
                'class' => 'text-left',
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
