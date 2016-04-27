<?php

namespace Seahinet\Admin\Controller;

use Seahinet\Lib\Controller\AuthActionController;

class DashboardController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_dashboard');
    }

    public function visitorsAction()
    {
        $cache = $this->getContainer()->get('cache');
        $visitors = $cache->fetch('UV', 'STAT_');
        return $visitors;
    }

}
