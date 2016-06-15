<?php

namespace Seahinet\Oauth\Controller;

use Seahinet\Admin\Model\User;
use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Oauth\Model\Collection\Token as TokenCollection;
use Seahinet\Oauth\Model\Consumer;
use Seahinet\Oauth\Model\Token;
use Zend\Math\Rand;

class AuthController extends ActionController
{

    public function dispatch($request = null, $routeMatch = null)
    {
        if (!isset($_SERVER['HTTPS'])) {
            return $this->getResponse()->withStatus(403, 'SSL required');
        }
        return parent::dispatch($request, $routeMatch);
    }

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
                            $code = Rand::getString(32, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
                        } while ($cache->fetch('$AUTHORIZATION_CODE$' . $code, 'OAUTH_'));
                        $callback = base64_decode($data['redirect_url']);
                        $cache->save('$AUTHORIZATION_CODE$' . $code, [
                            'consumer_id' => $consumer->getId(),
                            'user_id' => $user->getId(),
                            'redirect_url' => $callback
                                ], 'OAUTH_', 600);
                        return $this->redirect($callback .
                                        (strpos($callback, '?') === false ? '?' : '&') .
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
            if (isset($data['code']) && isset($data['client_id']) &&
                    isset($data['client_secret']) && isset($data['redirect_url'])) {
                $cache = $this->getContainer()->get('cache');
                $info = $cache->fetch('$AUTHORIZATION_CODE$' . $data['code'], 'OAUTH_');
                if ($info) {
                    $consumer = new Consumer;
                    $consumer->load($info['consumer_id']);
                    if ($consumer['key'] != $data['client_id'] || $consumer['secret'] != $data['client_secret']) {
                        return $this->getResponse()->withStatus(400);
                    }
                    if ($consumer->getId() && base64_decode($data['redirect_url']) === $info['redirect_url']) {
                        $user = $consumer['role_id'] === -1 ? (new User) : (new Customer);
                        $user->load($info['user_id']);
                        if ($user->getId()) {
                            $constraint = [
                                'consumer_id' => $consumer->getId(),
                                ($consumer['role_id'] === -1 ? 'admin_id' : 'customer_id') => $user->getId()
                            ];
                            $collection = new TokenCollection;
                            $collection->columns(['open_id'])->where($constraint);
                            if (!count($collection)) {
                                do {
                                    $constraint['open_id'] = Rand::getString(32, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
                                    $collection->reset('where')->where($constraint);
                                } while (count($collection));
                                $token = new Token;
                                $token->setData($constraint)->save();
                                $openId = $constraint['open_id'];
                            } else {
                                $openId = $collection[0]['open_id'];
                            }
                            do {
                                $code = Rand::getString(32, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
                            } while ($cache->fetch('$ACCESS_TOKEN$' . $code, 'OAUTH_'));
                            $cache->save('$ACCESS_TOKEN$' . $code, ['consumer_id' => $consumer->getId(), 'user_id' => $user->getId(), 'open_id' => $openId], 'OAUTH_', 3600);
                            return ['access_token' => $code, 'open_id' => $openId, 'expired_at' => date('l, d-M-Y H:i:s T', time() + 3600)];
                        }
                    }
                }
            }
            return $this->getResponse()->withStatus(400);
        }
        return $this->getResponse()->withStatus(405);
    }

    public function touchAction()
    {
        $token = $this->getRequest()->getQuery('access_token');
        $cache = $this->getContainer()->get('cache');
        $data = $cache->fetch('$ACCESS_TOKEN$' . $token, 'OAUTH_');
        if ($data) {
            $cache->save('$ACCESS_TOKEN$' . $token, $data, 'OAUTH_', 3600);
            return ['access_token' => $token, 'expired_at' => date('l, d-M-Y H:i:s T', time() + 3600)];
        }
        return $this->getResponse()->withStatus(400);
    }

}
