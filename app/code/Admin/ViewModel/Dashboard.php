<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\ViewModel\AbstractViewModel;

class Dashboard extends AbstractViewModel
{

    protected $config = [];

    public function getConfig()
    {
        if (empty($this->config)) {
            $this->config = $this->getContainer()->get('config')['stat'];
        }
        return $this->config;
    }

}
