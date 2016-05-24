<?php

namespace Seahinet\Admin\Controller;

use Exception;
use Gregwar\Captcha\CaptchaBuilder;
use Seahinet\Admin\Model\User;
use Seahinet\Email\Model\Template;
use Seahinet\Lib\Controller\ActionController;
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
            if ($segment->get('user')->getRole()->hasPermission('Admin\\Dashboard::index')) {
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
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['username', 'password'], 'admin');
            if ($result['error'] === 0) {
                $user = new User;
                if ($user->login($data['username'], $data['password'])) {
                    $result['message'][] = ['message' => $this->translate('Welcome %s. Last Login: %s', [$data['username'], $user['logdate']]), 'level' => 'success'];
                    $user->setData([
                        'logdate' => gmdate('Y-m-d h:i:s'),
                        'lognum' => $user->offsetGet('lognum') + 1
                    ])->save();
                    return $this->redirectLoggedin();
                } else {
                    $result['message'][] = ['message' => $this->translate('Login failed. Invalid username or password.'), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result, $this->getAdminUrl());
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
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['username'], 'admin');
            if ($result['error'] === 0) {
                $user = new User;
                $user->load($data['username'], 'username');
                if ($user->getId()) {
                    $token = md5(random_bytes(32));
                    $user->setData([
                        'rp_token' => $token,
                        'rp_token_created_at' => date('Y-m-d h:i:s')
                    ])->save();
                    try {
                        $mailer = $this->getContainer()->get('mailer');
                        $mailer->send((new Template)->load('forgot_password', 'code')->getMessage(['{{link}}' => $this->getAdminUrl('index/reset/?token=' . $token)])->addFrom('idriszhang@seahinet.com')->addTo($user->offsetGet('email'), $user->offsetGet('username')));
                        $result['message'][] = ['message' => $this->translate('You will receive an email with a link to reset your password.'), 'level' => 'success'];
                    } catch (Swift_SwiftException $e) {
                        $this->getContainer()->get('log')->logException($e);
                        $result['error'] = 1;
                        $result['message'][] = ['message' => $this->translate('An error detected while email transporting. Please try again later.'), 'level' => 'danger'];
                    } catch (Exception $e) {
                        $this->getContainer()->get('log')->logException($e);
                        $result['error'] = 1;
                        $result['message'][] = ['message' => $this->translate('An error detected. Please try again later.'), 'level' => 'danger'];
                    }
                } else {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('The username does not exist.'), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result, ':ADMIN');
    }

    public function resetPostAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['username', 'password'], 'admin');
            if ($result['error'] === 0) {
                if (empty($data['cpassword']) || $data['cpassword'] !== $data['password']) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('The confirm password is not equal to the password.'), 'level' => 'danger'];
                } else {
                    $user = new User;
                    $user->load($data['username'], 'username');
                    if ($user->getId() && $data['token'] == $user->offsetGet('rp_token') && time() <= strtotime($user->offsetGet('rp_token_created_at')) + 86400) {
                        $user->setData(['password' => (new Bcrypt)->create($data['password']), 'rp_token' => null, 'rp_token_created_at' => null])
                                ->save();
                        $result['message'][] = ['message' => $this->translate('The password has been reset successfully.'), 'level' => 'success'];
                    } else {
                        $result['error'] = 1;
                        $result['message'][] = ['message' => $this->translate('Invalid token.'), 'level' => 'danger'];
                    }
                }
            }
        }
        return $this->response($result, ':ADMIN');
    }

}
