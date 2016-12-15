<?php

namespace Seahinet\Admin\Controller;

use Seahinet\Log\Model\Collection\Visitor;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;
use Zend\Db\Sql\Expression;

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
        $collection->group('session_id')
                ->columns([new Expression('1')]);
        $segment = new Segment('admin');
        if ($id = $segment->get('user')->offsetGet('store_id')) {
            $collection->where(['store_id' => $id]);
        }
        return $this->stat($collection, function($collection) {
                    return count($collection);
                }
        );
    }

}
