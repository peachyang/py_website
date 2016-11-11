<?php

namespace Seahinet\Retailer\ViewModel\Dashboard;

use Seahinet\Retailer\ViewModel\AbstractViewModel;

class Stat extends AbstractViewModel
{

    public function getStat()
    {
        return $this->getConfig()['stat'];
    }

}
