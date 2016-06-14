<?php

namespace Seahinet\Oauth\Controller;

use Seahinet\Admin\Model\User;
use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\Controller\ApiActionController;
use Seahinet\Oauth\Model\Consumer;
use Zend\Math\Rand;

class AuthController extends ApiActionController
{

    public function indexAction()
    {
        $query = $this->getRequest()->getQuery();
        if (!isset($query['response_type']) ||
                $query['response_type'] !== 'code' ||
                !isset($query['client_id']) ||
                !isset($query['redirect_url'])) {
            return $this->getResponse()->withStatus('400');
        }
        $consumer = new Consumer;
        $consumer->load($query['client_id'], 'key');
        if (!$consumer->getId() && strpos(base64_decode($query['redirect_url']), $consumer['callback_url']) !== 0) {
            return $this->getResponse()->withStatus('400');
        } else {
            $root = $this->getLayout('oauth_login');
            $root->getChild('form', true)->setConsumer($consumer);
            return $root;
        }
    }

    public function loginAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['username', 'password', 'response_type', 'client_id']);
            if ($result['error'] === 0) {
                $consumer = new Consumer;
                $consumer->load($data['client_id'], 'key');
                if ($consumer->getId()) {
                    $user = $consumer['role_id'] === -1 ? (new User) : (new Customer);
                    if ($user->valid($data['username'], $data['password'])) {
                        $cache = $this->getContainer()->get('cache');
                        do {
                            $code = Rand::getString(32, 'abcdefghijklmnopqrstuvwxyz0123456789');
                        } while ($cache->fetch($code, 'OAUTH_'));
                        $cache->save($code, ['consumer_id' => $consumer->getId(), 'user_id' => $user->getId()], 'OAUTH_', 600);
                        $callback = base64_decode($data['redirect_url']);
                        return $this->redirect($callback .
                                        (strpos($callback, '?') === -1 ? '?' : '&') .
                                        'authorization_code=' . $code .
                                        (isset($data['state']) ?
                                                '&state=' . $data['state'] : ''));
                    }
                } else {
                    return $this->getResponse()->withStatus('400');
                }
            }
        }
        if (isset($result)) {
            $this->addMessage($result['message'], 'danger', 'oauth');
        }
        unset($data['csrf']);
        unset($data['username']);
        unset($data['password']);
        return $this->redirectReferer('oauth/auth/?' . http_build_query($data));
    }

    public function tokenAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['code', 'client_id', 'redirect_uri', 'client_secret']);
            if ($result['error'] === 0) {
                $info = $cache->fetch($data['code'], 'OAUTH_');
                if ($info) {
                    $consumer = new Consumer;
                    $consumer->load($info['consumer_id']);
                    if ($consumer->getId() && strpos(base64_decode($query['redirect_url']), $consumer['callback_url']) === 0) {
                        $user = $consumer['role_id'] === -1 ? (new User) : (new Customer);
                        $user->load($info['user_id']);
                        if ($user->getId()) {
                            
                        }
                    }
                }
            }
            return $this->getResponse()->withStatus(401);
        }
        return $this->getResponse()->withStatus(405);
    }

}
