<?php

namespace Seahinet\Admin\ViewModel\CMS;

use Seahinet\CMS\Model\Collection\Page as Collection;
use Seahinet\Lib\ViewModel\Grid;

class Page extends Grid
{

    public function __construct()
    {
        $this->setVariable('title', 'Page Management');
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

    protected function prepareCollection(\Seahinet\Lib\Model\AbstractCollection $collection = null)
    {
        return parent::prepareCollection(new Collection);
    }

}
