<?php

namespace Seahinet\Admin\Controller\Sales;

use Seahinet\Lib\Controller\AuthActionController;

class RefundController extends AuthActionController
{

    use \Seahinet\Lib\Traits\DB;
    public function indexAction()
    {
        $root = $this->getLayout('admin_refund_list');
        return $root;
    }
    
    public function viewAction()
    {
        $root = $this->getLayout('admin_refund_view');
        return $root;
    }
    

}
