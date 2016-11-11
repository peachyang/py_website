<?php

namespace Seahinet\Retailer\Controller;

use DateTime;

class DashboardController extends AuthActionController
{

    use \Seahinet\Admin\Traits\Stat;

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
