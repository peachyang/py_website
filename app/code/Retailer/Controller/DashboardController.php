<?php

namespace Seahinet\Retailer\Controller;

use Seahinet\Log\Model\Collection\Visitor;
use Zend\Db\Sql\Expression;

class DashboardController extends AuthActionController
{

    use \Seahinet\Admin\Traits\Stat;

    public function visitorsAction()
    {
        $collection = new Visitor;
        $collection->group('session_id')
                ->columns([new Expression('1')])
                ->where(['store_id' => $this->getRetailer()->offsetGet('store_id')]);
        return $this->stat($collection, function($collection) {
                    return count($collection);
                }
        );
    }

}
