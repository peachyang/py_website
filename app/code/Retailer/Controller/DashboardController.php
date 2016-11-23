<?php

namespace Seahinet\Retailer\Controller;

use DateTime;
use Seahinet\Log\Model\Collection\Visitor;

class DashboardController extends AuthActionController
{

    use \Seahinet\Admin\Traits\Stat;

    public function visitorsAction()
    {
        $collection = new Visitor;
        $collection->group('session_id')
                ->columns(['created_at'])
                ->where(['store_id' => $this->getRetailer()->offsetGet('store_id')])
        ->where->greaterThanOrEqualTo('created_at', date('Y-m-d h:i:s', strtotime('-1year')));
        return $this->stat($collection, function($item) {
                    return new DateTime(date(DateTime::RFC3339, strtotime($item['created_at'])));
                }
        );
    }

}
