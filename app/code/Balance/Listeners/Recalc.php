<?php

namespace Seahinet\Balance\Listerers;

use Seahinet\Lib\Listeners\ListenerInterface;

class Recalc implements ListenerInterface
{

    use \Seahinet\RewardPoints\Traits\Recalc;

    public function afterCustomerLogin($event)
    {
        $customer = $event['model'];
        $this->recalc($customer->getId());
    }

}
