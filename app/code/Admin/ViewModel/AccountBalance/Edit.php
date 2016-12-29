<?php

namespace Seahinet\Admin\ViewModel\AccountBalance;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\ViewModel\Template;

class Edit extends Template
{
    protected $customer = null;

    public function getCustomer()
    {
        if (is_null($this->customer) && $this->getQuery('id')) {
            $this->customer = new Customer;
            $this->customer->load($this->getQuery('id'));
        }
        return $this->customer;
    }

    public function getCurrentBalances()
    {
        if ($this->getCustomer()) {
            echo 1;
            exit();
            return (int) $this->getCustomer()->offsetGet('balance');
        }
        return 0;
    }
}
