<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\ViewModel\AbstractViewModel;

class Dashboard extends AbstractViewModel
{

    public function getConfig()
    {
        if (is_null($this->config)) {
            $this->config = $this->getContainer()->get('config')['stat'];
        }
        return $this->config;
    }

}
