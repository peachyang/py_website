<?php

namespace Seahinet\Balance\ViewModel;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Cart;

class Checkout extends Template
{

    use \Seahinet\Balance\Traits\Calc;

    public function hasLoggedIn()
    {
        $segment = new Segment('customer');
        return $segment->get('hasLoggedIn');
    }

    public function getCurrentBalances()
    {
        if ($this->hasLoggedIn()) {
            $segment = new Segment('customer');
            $customer = new Customer;
            $customer->load($segment->get('customer')->getId());
            return (float) $customer->getBalance();
        }
        return 0;
    }

    public function getAvailableBalances()
    {
        if ($this->hasLoggedIn()) {
            return $this->getBalances(Cart::instance());
        }
        return 0;
    }

    public function isVirtual()
    {
        return Cart::instance()->isVirtual();
    }

    public function hasApplied()
    {
        $additional = Cart::instance()->offsetGet('additional');
        return $additional && !empty(@json_decode($additional, true)['balance']);
    }

}
