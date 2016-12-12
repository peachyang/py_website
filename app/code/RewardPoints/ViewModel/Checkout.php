<?php

namespace Seahinet\RewardPoints\ViewModel;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Cart;

class Checkout extends Template
{

    use \Seahinet\RewardPoints\Traits\Calc;

    public function hasLoggedIn()
    {
        $segment = new Segment('customer');
        return $segment->get('hasLoggedIn');
    }

    public function getCurrentPoints()
    {
        if ($this->hasLoggedIn()) {
            $segment = new Segment('customer');
            $customer = new Customer;
            $customer->load($segment->get('customer')->getId());
            return (int) $customer->offsetGet('rewardpoints');
        }
        return 0;
    }

    public function getAvailablePoints()
    {
        if ($this->hasLoggedIn()) {
            return $this->getPoints(Cart::instance());
        }
        return 0;
    }

    public function hasApplied()
    {
        $additional = Cart::instance()->offsetGet('additional');
        return $additional && !empty(@json_decode($additional, true)['rewardpoints']);
    }

}
