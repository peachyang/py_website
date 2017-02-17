<?php

namespace Seahinet\Retailer\Controller;

use Seahinet\Lib\Session\Segment;
use Seahinet\Customer\Controller\AuthActionController;

class StatementColler extends AuthActionController
{

    public function balanceAction()
    {
        return $this->getLayout('retailer_balance_statement');
    }

}
