<?php

namespace Seahinet\Admin\Controller\Email;

use Exception;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Mailer;
use Seahinet\Email\Model\Queue as Model;
use Seahinet\Email\Model\Collection\Subscriber;
use Swift_Message;

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
            $result = $this->validateForm($data, $data['rcpt'] ? ['template_id', 'emails', 'datetime'] : ['template_id', 'datetime']);
            if (!$result['error']) {
                try {
                    if ($data['rcpt']) {
                        $emails = explode(';', $data['emails']);
                    } else {
                        $collection = new Subscriber;
                        $collection->where(['status' => 1]);
                        $emails = $collection->load(true, true)->toArray();
                    }
                    $config = $this->getContainer()->get('config');
                    $from = $config['email/newsletter/sender'];
                    foreach ($emails as $email) {
                        $model = new Model;
                        $model->setData([
                            'template_id' => $data['template_id'],
                            'from' => $from,
                            'to' => is_scalar($email) ? $email : $email['email'],
                            'scheduled_at' => date('Y-m-d H:i:s', strtotime($data['datetime']))
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
        return $this->doDelete('\\Seahinet\\Email\\Model\\Queue', ':ADMIN/email_queue/');
    }

    public function testAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $config = [];
            foreach ($data as $key => $value) {
                $config['email/' . $key] = $value;
            }
            $config['email/transport/enable'] = 1;
            try {
                $mailer = new Mailer($config);
                $message = new Swift_Message('This is a testing message.', 'This is a testing message from ' . Bootstrap::getMerchant()['name'] . '.');
                $message->addFrom($config['email/transport/username'])
                        ->addTo($config['email/transport/username']);
                $mailer->send($message);
                $result['message'][] = ['message' => $this->translate('The transportation works well.'), 'level' => 'success'];
                $result['error'] = 0;
            } catch (Exception $e) {
                $result['message'][] = ['message' => $e->getMessage(), 'level' => 'danger'];
                $result['error'] = 1;
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], $this->getRequest()->getHeader('HTTP_REFERER'));
    }

}
