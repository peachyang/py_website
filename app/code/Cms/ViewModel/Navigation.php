<?php

namespace Seahinet\Cms\ViewModel;

use Seahinet\Lib\ViewModel\Template;

class Navigation extends Template
{

    protected $navigationModel = null;

    public function getNavigationModel()
    {
        return $this->navigationModel;
    }

    public function setNavigationModel()
    {
        $this->navigationModel = $navigationModel;
        return $this;
    }

}
