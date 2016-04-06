<?php

namespace Seahinet\Admin\Controller;

use Seahinet\Admin\Model\User;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;
use Gregwar\Captcha\CaptchaBuilder;

class IndexController extends ActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_login');
    }

    public function loginAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (!isset($data['form_key']) || !$this->validateFormKey($data['form_key'])) {
                $this->addMessage('The form submitted did not originate from the expected site.', 'danger', 'admin');
                return $this->redirectReferer();
            }
            if (!isset($data['username'])) {
                $this->addMessage('The username field is required and can not be empty.', 'danger', 'admin');
                return $this->redirectReferer();
            }
            if (!isset($data['password'])) {
                $this->addMessage('The password field is required and can not be empty.', 'danger', 'admin');
                return $this->redirectReferer();
            }
            if (!isset($data['captcha']) || !$this->validateCaptcha($data['captcha'])) {
                $this->addMessage('The captcha value is wrong.', 'danger', 'admin');
                return $this->redirectReferer();
            }
            $user = new User;
            if ($user->login($data['username'], $data['password'])) {
                return $this->redirect();
            } else {
                $this->addMessage('Login failed. Invalid username or password.', 'danger', 'admin');
            }
        }
        return $this->redirectReferer();
    }

    protected function validateCaptcha($value)
    {
        $segment = new Segment('admin');
        return $segment->get('captcha') == $value;
    }

    public function captchaAction()
    {
        $builder = new CaptchaBuilder();
        $builder->setBackgroundColor(0xff, 0xff, 0xff);
        $builder->build();
        $segment = new Segment('admin');
        $segment->set('captcha', $builder->getPhrase());
        header('Content-type: image/jpeg');
        header('Cache-Control: no-store');
        $builder->output();
        exit;
    }

}
