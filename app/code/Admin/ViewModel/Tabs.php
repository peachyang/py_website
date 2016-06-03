<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\ViewModel\AbstractViewModel;

class Tabs extends AbstractViewModel
{

    protected $tabs = [];
    protected $generateTabPane = true;
    protected $mainTabLabel = false;

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

    public function hasTab($id)
    {
        return isset($this->tabs[$id]);
    }

    public function addTab($id, $tab)
    {
        return $this->tabs[$id] = $tab;
    }

    public function getMainTabLabel()
    {
        return $this->mainTabLabel;
    }

    public function setMainTabLabel($mainTabLabel)
    {
        $this->mainTabLabel = $mainTabLabel;
        return $this;
    }

    public function generateTabPane($flag = null)
    {
        if (is_bool($flag)) {
            $this->generateTabPane = $flag;
        }
        return $this->generateTabPane;
    }

}
