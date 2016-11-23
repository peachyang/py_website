<?php

namespace Seahinet\Admin\Controller;

use DateTime;
use Seahinet\Log\Model\Collection\Visitor;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;

class DashboardController extends AuthActionController
{

    use \Seahinet\Admin\Traits\Stat;

    public function indexAction()
    {
        return $this->getLayout('admin_dashboard');
    }

    public function visitorsAction()
    {
        $collection = new Visitor;
        $collection->group('session_id')->columns(['created_at'])
        ->where->greaterThanOrEqualTo('created_at', date('Y-m-d h:i:s', strtotime('-1year')));
        $segment = new Segment('admin');
        if ($id = $segment->get('user')->offsetGet('store_id')) {
            $collection->where(['store_id' => $id]);
        }
        return $this->stat($collection, function($item) {
                    return new DateTime(date(DateTime::RFC3339, strtotime($item['created_at'])));
                }
        );
    }

}
