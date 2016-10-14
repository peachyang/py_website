<?php

namespace Seahinet\Admin\Controller\Customer;

use Seahinet\Customer\Model\CreditCard as Model;
use Seahinet\Lib\Controller\AuthActionController;

class CreditcardController extends AuthActionController
{

    public function indexAction()
    {
        $query = $this->getRequest()->getQuery();
        if (isset($query['id'])) {
            $model = new Model;
            $model->load($query['id']);
            if ($model->getId()) {
                $root = $this->getLayout('admin_customer_creditcard');
                $root->getChild('main', true)->setVariable('model', $model);
                return $root;
            }
        }
        return $this->notFoundAction();
    }

}
