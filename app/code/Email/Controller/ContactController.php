<?php

namespace Seahinet\Email\Controller;

use Exception;
use Seahinet\Lib\Controller\ActionController;
use Swift_Message;
use Swift_TransportException;

class ContactController extends ActionController
{

    public function indexAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getPost();
            $content = '';
            foreach ($data as $key => $value) {
                $content .= $key . ': ' . $value . '<br />';
            }
            try {
                $config = $this->getContainer()->get('config');
                $mailer = $this->getContainer()->get('mailer');
                $message = new Swift_Message();
                $message->setBody($content, 'text/html', 'UTF-8');
                $mailer->send($message->setSubject($this->translate('Contact Us'))
                                ->addFrom($config['email/customer/sender_email'] ?: $config['email/default/sender_email'], $config['email/customer/sender_name'] ?: $config['email/default/sender_name'])
                                ->addTo($config['email/customer/sender_email'] ?: $config['email/default/sender_email'], $config['email/customer/sender_name'] ?: $config['email/default/sender_name']));
            } catch (Swift_TransportException $e) {
                $this->getContainer()->get('log')->logException($e);
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('An error detected. Please try again later.'), 'level' => 'danger'];
            } catch (Exception $e) {
                $this->getContainer()->get('log')->logException($e);
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('An error detected. Please try again later.'), 'level' => 'danger'];
            }
        }
        return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'), 'customer');
    }

}
