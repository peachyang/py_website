<?php

namespace Seahinet\Balance\Controller;

use Seahinet\Customer\Controller\AuthActionController;

class StatementController extends AuthActionController
{

    public function IndexAction()
    {
        return $this->getLayout('balance_statement');
    }

}
