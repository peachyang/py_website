<?php

namespace Seahinet\Admin\Controller\Sales;

use Seahinet\Lib\Controller\AuthActionController;

class CartController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_sales_cart_list');
        return $root;
    }

    public function viewAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            return $this->getLayout('admin_sales_cart_view');
        }
        return $this->notFoundAction();
    }

}
