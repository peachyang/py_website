<?php

namespace Seahinet\Admin\ViewModel\CMS;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\CMS\Model\Collection\Page as Collection;
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
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'title' => 'Title',
            'uri_key' => 'Uri key',
            'language' => 'Language',
            'status' => 'Status'
        ];
    }

    protected function prepareCollection(AbstractCollection $collection = null)
    {
        return parent::prepareCollection(new Collection);
    }

}
