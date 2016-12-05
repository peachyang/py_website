<?php

namespace Seahinet\Customer\ViewModel;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\ViewModel\Template;

class Account extends Template
{

    protected static $customer = null;
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
        if (is_null(self::$customer)) {
            $segment = new Segment('customer');
            self::$customer = new Customer;
            self::$customer->load($segment->get('customer')->getId(), 'id');
        }
        return self::$customer;
    }

}
