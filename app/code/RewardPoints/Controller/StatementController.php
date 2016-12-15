<?php

namespace Seahinet\RewardPoints\Controller;

use Seahinet\Customer\Controller\AuthActionController;

class StatementController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('rewardpoints_statement');
    }

}
