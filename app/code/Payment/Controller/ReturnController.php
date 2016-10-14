<?php

namespace Seahinet\Payment\Controller;

use Seahinet\Lib\Controller\ActionController;

class ReturnController extends ActionController
{

    public function indexAction()
    {
        if ($this->getRequest()->isGet()) {
            $data = $this->getRequest()->getQuery();
        }
        return $this->notFoundAction();
    }

}
