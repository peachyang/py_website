<?php

namespace Seahinet\Sales\ViewModel\Refund;

use Seahinet\Customer\ViewModel\Account;
use Seahinet\Sales\Source\Refund\{
    Service,
    Status
};

class View extends Account
{

    protected $status = null;
    protected $service = null;

    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

    public function getStatus($key)
    {
        if (is_null($this->status)) {
            $this->status = (new Status)->getSourceArray();
        }
        return $this->status[$key] ?? '';
    }

    public function getService($key)
    {
        if (is_null($this->service)) {
            $this->service = (new Service)->getSourceArray();
        }
        return $this->service[$key] ?? '';
    }

}
