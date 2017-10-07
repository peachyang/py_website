<?php

namespace Seahinet\Admin\Controller\Email;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Email\Model\Subscriber as Model;

class SubscriberController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_email_subscriber_list');
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Email\\Model\\Subscriber', ':ADMIN/email_subscriber/');
    }

}
