<?php

namespace Seahinet\Admin\Controller;

use Exception;
use Gregwar\Captcha\CaptchaBuilder;
use Seahinet\Admin\Model\User;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Model\Email\Template;
use Seahinet\Lib\Session\Segment;
use Swift_SwiftException;
use Zend\Crypt\Password\Bcrypt;

class IndexController extends ActionController
{

    public function dispatch($request = null, $routeMatch = null)
    {
        $result = $this->redirectLoggedin();
        if ($result !== false) {
            return $result;
        }
        return parent::dispatch($request, $routeMatch);
    }

    protected function redirectLoggedin()
    {
        $segment = new Segment('admin');
        if ($segment->get('isLoggedin')) {
            if ($segment->get('user')->getRole()->hasPermission('Seahinet\\Admin\\Controller\\DashboardController::indexAction')) {
                return $this->redirect(':ADMIN/dashboard/');
            } else {
                return $this->redirect(':ADMIN/user/');
            }
        }
        return false;
    }

    public function indexAction()
    {
        return $this->getLayout('admin_login');
    }

    public function loginAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (empty($data['csrf']) || !$this->validateCsrfKey($data['csrf'])) {
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
                $this->addMessage($this->translate('Welcome %s. Last Login: %s', [$data['username'], $user['logdate']]), 'success', 'admin');
                $user->setData([
                    'logdate' => gmdate('Y-m-d h:i:s'),
                    'lognum' => $user->offsetGet('lognum') + 1
                ])->save();
                return $this->redirectLoggedin();
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
        $builder->build(70, 26);
        $segment = new Segment('admin');
        $segment->set('captcha', strtoupper($builder->getPhrase()));
        $this->getResponse()
                ->withHeader('Content-type', 'image/jpeg')
                ->withHeader('Cache-Control', 'no-store');
        return $builder->get();
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
            if (empty($data['csrf']) || !$this->validateCsrfKey($data['csrf'])) {
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
                $token = md5(random_bytes(32));
                $user->setData([
                    'rp_token' => $token,
                    'rp_token_created_at' => time() + 86400
                ])->save();
                try {
                    $mailer = $this->getContainer()->get('mailer');
                    $mailer->send((new Template)->load('forgot_password', 'code')->getMessage(['{{link}}' => $this->getAdminUrl('index/reset/?token=' . $token)])->addFrom('idriszhang@seahinet.com')->addTo($user->offsetGet('email'), $user->offsetGet('username')));
                    $this->addMessage($this->translate('You will receive an email with a link to reset your password.'), 'success', 'admin');
                    return $this->redirect(':ADMIN');
                } catch (Swift_SwiftException $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $this->addMessage($this->translate('An error detected while email transporting.'), 'danger', 'admin');
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $this->addMessage($this->translate('An error detected. Please try again later.'), 'danger', 'admin');
                }
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
            if (empty($data['csrf']) || !$this->validateCsrfKey($data['csrf'])) {
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
                $user->setData(['password' => (new Bcrypt)->create($data['password']), 'rp_token' => null, 'rp_token_created_at' => null])->save();
                $this->addMessage($this->translate('The password has been reset successfully.'), 'success', 'admin');
                return $this->redirect(':ADMIN');
            } else {
                $this->addMessage($this->translate('Invalid token.'), 'danger', 'admin');
            }
        }
        return $this->redirectReferer();
    }

}
