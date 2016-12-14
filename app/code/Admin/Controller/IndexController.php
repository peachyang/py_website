<?php

namespace Seahinet\Admin\Controller;

use Exception;
use Gregwar\Captcha\CaptchaBuilder;
use Seahinet\Admin\Model\User;
use Seahinet\Email\Model\Template as TemplateModel;
use Seahinet\Email\Model\Collection\Template as TemplateCollection;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;
use Swift_TransportException;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;

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
        if ($segment->get('hasLoggedIn')) {
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
                    $this->getContainer()->get('log')->log($data['username'] . ' has logged in', 200);
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
        $config = $this->getContainer()->get('config');
        $phrase = Rand::getString($config['customer/captcha/number'], $config['customer/captcha/symbol']);
        $file = BP . 'var/captcha/' . md5($phrase) . '.jpg';
        if (file_exists($file)) {
            $result = file_get_contents($file);
        } else {
            if (!is_dir(BP . 'var/captcha')) {
                mkdir(BP . 'var/captcha', 0644);
            }
            $builder = new CaptchaBuilder($phrase);
            $builder->setBackgroundColor(0xff, 0xff, 0xff);
            $builder->build(70, 26);
            $builder->save($file);
            $result = $builder->get();
        }
        $segment = new Segment('admin');
        $segment->set('captcha', strtoupper($phrase));
        $this->getResponse()
                ->withHeader('Content-type', 'image/jpeg')
                ->withHeader('Cache-Control', 'no-store');
        return $result;
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
                    $token = Rand::getString(32);
                    $user->setData([
                        'rp_token' => $token,
                        'rp_token_created_at' => date('Y-m-d h:i:s')
                    ])->save();
                    try {
                        $config = $this->getContainer()->get('dbAdapter');
                        $collection = new TemplateCollection;
                        $collection->join('email_template_language', 'email_template_language.template_id=email_template.id', [], 'left')
                                ->where([
                                    'code' => $config['email/customer/confirm_template'],
                                    'language_id' => \Seahinet\Lib\Bootstrap::getLanguage()->getId()
                        ]);
                        if (count($collection)) {
                            $mailer = $this->getContainer()->get('mailer');
                            $mailer->send((new TemplateModel($collection[0]))
                                            ->getMessage(['{{link}}' => $this->getAdminUrl('index/reset/?token=' . $token)])
                                            ->addFrom($config['email/admin/sender_email'] ?: $config['email/default/sender_email'], $config['email/admin/sender_name'] ?: $config['email/default/sender_name'])
                                            ->addTo($user->offsetGet('email'), $user->offsetGet('username')));
                        }
                        $result['message'][] = ['message' => $this->translate('You will receive an email with a link to reset your password.'), 'level' => 'success'];
                    } catch (Swift_TransportException $e) {
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
