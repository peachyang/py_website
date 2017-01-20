<?php

namespace Seahinet\Balance\Controller;

use Exception;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Sales\Model\Cart;

class IndexController extends ActionController
{

    public function loadAction()
    {
        return $this->getLayout('checkout_order_balance');
    }

}
