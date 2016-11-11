<?php

namespace Seahinet\Admin\Controller;

use DateTime;
use Seahinet\Lib\Controller\AuthActionController;

class DashboardController extends AuthActionController
{

    use \Seahinet\Admin\Traits\Stat;

    public function indexAction()
    {
        return $this->getLayout('admin_dashboard');
    }

    public function visitorsAction()
    {
        $cache = $this->getContainer()->get('cache');
        $visitors = $cache->fetch('UV', 'STAT_');
        return $this->stat($visitors, function($count, $time) {
                    return new DateTime(is_numeric($time) ? date(DateTime::RFC3339, $time) : $time);
                }, function($count) {
                    return $count;
                });
    }

}
