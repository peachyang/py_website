<?php

namespace Seahinet\Admin\ViewModel\RewardPoints;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\ViewModel\Template;

class Edit extends Template
{

    public function getCurrentPoints()
    {
        if ($this->getQuery('id')) {
            $customer = new Customer;
            $customer->load($this->getQuery('id'));
            return (int) $customer->offsetGet('rewardpoints');
        }
        return 0;
    }

}
