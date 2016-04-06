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
            if (empty($data['form_key']) || !$this->validateFormKey($data['form_key'])) {
                $this->addMessage($this->translate('The form submitted did not originate from the expected site.'), 'danger', 'admin');
                return $this->redirectReferer();
            }
            if (empty($data['username'])) {
                $this->addMessage($this->translate('The username field is required and can not be empty.'), 'danger', 'admin');
                return $this->redirectReferer();
            }
            if (empty($data['password'])) {
                $this->addMessage($this->translate('The password field is required and can not be empty.'), 'danger', 'admin');
                return $this->redirectReferer();
            }
            if (empty($data['captcha']) || !$this->validateCaptcha($data['captcha'], 'admin')) {
                $this->addMessage($this->translate('The captcha value is wrong.'), 'danger', 'admin');
                return $this->redirectReferer();
            }
            $user = new User;
            if ($user->login($data['username'], $data['password'])) {
                $this->addMessage($this->translate('Welcome %s. Last Login: %s', $data['username'], $data['logdate']), 'success', 'admin');
                $user->setData([
                    'logdate' => time(),
                    'lognum' => $user->offsetGet('lognum') + 1
                ])->save();
                return $this->redirect();
            } else {
                $this->addMessage($this->translate('Login failed. Invalid username or password.'), 'danger', 'admin');
            }
        }
        return $this->redirectReferer();
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

    public function forgotAction()
    {
        return $this->getLayout('admin_forgot_password');
    }

    public function resetAction()
    {
        if (!$this->getRequest()->getQuery('token')) {
            return $this->redirect(':ADMIN');
        }
        return $this->getLayout('admin_reset_password');
    }

    public function forgotPostAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (empty($data['form_key']) || !$this->validateFormKey($data['form_key'])) {
                $this->addMessage($this->translate('The form submitted did not originate from the expected site.'), 'danger', 'admin');
                return $this->redirectReferer();
            }
            if (empty($data['username'])) {
                $this->addMessage($this->translate('The username field is required and can not be empty.'), 'danger', 'admin');
                return $this->redirectReferer();
            }
            if (empty($data['captcha']) || !$this->validateCaptcha($data['captcha'], 'admin')) {
                $this->addMessage($this->translate('The captcha value is wrong.'), 'danger', 'admin');
                return $this->redirectReferer();
            }
            $user = new User;
            $user->load($data['username'], 'username');
            if ($user->getId()) {
                $token = random_bytes(32);
                $user->setData([
                    'rp_token' => $token,
                    'rp_token_created_at' => time() + 86400
                ])->save();
//                $mailer = $this->getContainer()->get('mailer');
//                $mailer->send();
                $this->addMessage($this->translate('You will receive an email with a link to reset your password.'), 'success', 'admin');
                return $this->redirect(':ADMIN');
            } else {
                $this->addMessage($this->translate('The username does not exist.'), 'danger', 'admin');
            }
        }
        return $this->redirectReferer();
    }

    public function resetPostAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (empty($data['form_key']) || !$this->validateFormKey($data['form_key'])) {
                $this->addMessage($this->translate('The form submitted did not originate from the expected site.'), 'danger', 'admin');
                return $this->redirectReferer();
            }
            if (empty($data['username'])) {
                $this->addMessage($this->translate('The username field is required and can not be empty.'), 'danger', 'admin');
                return $this->redirectReferer();
            }
            if (empty($data['password'])) {
                $this->addMessage($this->translate('The password field is required and can not be empty.'), 'danger', 'admin');
                return $this->redirectReferer();
            } else if (empty($data['cpassword']) || $data['cpassword'] !== $data['password']) {
                $this->addMessage($this->translate('The confirm password is not equal to the password.'), 'danger', 'admin');
                return $this->redirectReferer();
            }
            if (empty($data['captcha']) || !$this->validateCaptcha($data['captcha'], 'admin')) {
                $this->addMessage($this->translate('The captcha value is wrong.'), 'danger', 'admin');
                return $this->redirectReferer();
            }
            $user = new User;
            $user->load($data['username'], 'username');
            if ($user->getId() && $data['token'] == $user->offsetGet('rp_token') && time() <= $user->offsetGet('rp_token_created_at')) {
                $user->setData(['password' => $data['password'], 'rp_token' => null, 'rp_token_created_at' => null])->save();
                $this->addMessage($this->translate('The password has been reset successfully.'), 'success', 'admin');
                return $this->redirect(':ADMIN');
            } else {
                $this->addMessage($this->translate('Invalid token.'), 'danger', 'admin');
            }
        }
        return $this->redirectReferer();
    }

}
