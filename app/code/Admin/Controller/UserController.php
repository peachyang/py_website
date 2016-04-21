<?php

namespace Seahinet\Admin\Controller;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;

class UserController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_user');
    }

    public function logoutAction()
    {
        $segment = new Segment('admin');
        $segment->set('isLoggedin', false);
        $segment->offsetUnset('user');
        return $this->redirect(':ADMIN');
    }

}
