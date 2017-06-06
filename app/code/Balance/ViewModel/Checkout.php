<?php

namespace Seahinet\Balance\ViewModel;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Cart;

class Checkout extends Template
{

    use \Seahinet\Balance\Traits\Calc;

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

    public function getCurrency()
    {
        return Cart::instance()->getCurrency();
    }

    public function getAvailableBalances()
    {
        if ($this->hasLoggedIn()) {
            return $this->getBalances(Cart::instance());
        }
        return 0;
    }

    public function canUse()
    {
        $flag = true;
        $id = $this->getConfig()['balance/general/product_for_recharge'];
        if ($id) {
            foreach (Cart::instance()->getItems() as $item) {
                if ($id === $item['product_id']) {
                    $flag = false;
                }
            }
        } else {
            return false;
        }
        return $flag && $this->getSegment('customer')->get('hasLoggedIn') && $this->getConfig()['balance/general/enable'];
    }

    public function hasApplied()
    {
        $additional = Cart::instance()->offsetGet('additional');
        return $additional && !empty(@json_decode($additional, true)['balance']);
    }

}
