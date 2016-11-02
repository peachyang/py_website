<?php

namespace Seahinet\Sales\ViewModel\Refund;

use Seahinet\Customer\ViewModel\Account;

class View extends Account
{

    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

}
