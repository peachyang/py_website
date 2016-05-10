<?php

namespace Seahinet\Admin\Controller\Email;

use Exception;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Email\Model\Queue as Model;
use Seahinet\Email\Model\Collection\Subscriber;

class QueueController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_email_queue_list');
    }

    public function scheduleAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, $data['rcpt'] ? ['template_id','emails', 'datetime'] : ['template_id','datetime']);
            if (!$result['error']) {
                try {
                    if ($data['rcpt']) {
                        $emails = explode(';', $data['emails']);
                    } else {
                        $collection = new Subscriber;
                        $collection->where(['status' => 1]);
                        $emails = $collection->load()->toArray();
                    }
                    $config = $this->getContainer()->get('config');
                    $from = $config['email/newsletter/sender'];
                    foreach ($emails as $email) {
                        $model = new Model;
                        $model->setData([
                            'template_id' => $data['template_id'],
                            'from' => $from,
                            'to' => $email,
                            'scheduled_at' => date('Y-m-d h:i:s',strtotime($data['datetime']))
                        ])->save();
                    }
                    $result['message'][] = ['message' => $this->translate('Scheduled successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while scheduling. Please check the log report or try again.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
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