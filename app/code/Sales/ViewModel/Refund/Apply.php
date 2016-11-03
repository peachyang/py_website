<?php

namespace Seahinet\Sales\ViewModel\Refund;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Source\Refund\{
    Reason,
    Service
};

class Apply extends Template
{

    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

    public function getReasons()
    {
        return (new Reason)->getSourceArray();
    }

    public function getServices()
    {
        return (new Service)->getSourceArray();
    }

}
