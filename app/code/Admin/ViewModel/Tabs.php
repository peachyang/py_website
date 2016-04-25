<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\ViewModel\AbstractViewModel;

class Tabs extends AbstractViewModel
{

    protected $tabs = [];

    public function __construct()
    {
        $this->setTemplate('admin/tabs');
    }

    public function getTabs()
    {
        return $this->tabs;
    }

    public function setTabs($tabs)
    {
        $this->tabs = $tabs;
        return $this;
    }

    public function getMainTabLabel()
    {
        return 'Info';
    }

}
