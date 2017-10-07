<?php

namespace Seahinet\Admin\ViewModel\Balance;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\I18n\Model\Currency;

class Edit extends Template
{

    protected $customer = null;
    protected static $currency = null;

    public function getCurrency()
    {
        if (is_null(self::$currency)) {
            self::$currency = $this->getContainer()->get('currency');
        }
        return self::$currency;
    }

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
            return (int) $this->getCustomer()->offsetGet('balance');
        }
        return 0;
    }

}
