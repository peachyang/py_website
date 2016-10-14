<?php

namespace Seahinet\Payment\Controller;

use Seahinet\Lib\Controller\ActionController;

class NotifyController extends ActionController
{

    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
        }
        return $this->notFoundAction();
    }

}
