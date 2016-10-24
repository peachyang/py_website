<?php

namespace Seahinet\Sales\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Sales\Model\Order;

class InquireController extends ActionController
{

    public function inquireAction()
    {
        return $this->getLayout('sales_order_inquire');
    }

}
