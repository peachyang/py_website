<?php

namespace Seahinet\AccountBalance\Controller;

use Seahinet\Lib\Controller\AuthActionController;

class IndexController extends AuthActionController
{

    public function IndexAction()
    {
        return $this->getLayout('accountbalance_detail');
    }

}
