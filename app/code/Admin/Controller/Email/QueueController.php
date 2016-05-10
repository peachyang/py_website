<?php

namespace Seahinet\Admin\Controller\Email;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Email\Model\Queue as Model;

class QueueController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_email_queue_list');
    }

    public function deleteAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isDelete()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data);
            if ($result['error'] === 0) {
                try {
                    $model = new Model;
                    $count = 0;
                    foreach ((array) $data['id'] as $id) {
                        $model->setId($id)->remove();
                        $count++;
                    }
                    $result['message'][] = ['message' => $this->translate('%d item(s) have been deleted successfully.', [$count]), 'level' => 'success'];
                    $result['removeLine'] = 1;
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while deleting. Please check the log report or try again.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        return $this->response($result, ':ADMIN/email_queue/');
    }

}
