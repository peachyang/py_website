<?php

namespace Seahinet\Admin\Controller;

use Seahinet\Lib\Controller\AuthActionController;

class DashboardController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_dashboard');
    }

}
