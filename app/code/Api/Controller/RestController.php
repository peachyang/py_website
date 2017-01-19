<?php

namespace Seahinet\Api\Controller;

use Exception;
use Seahinet\Admin\Model\User;
use Seahinet\Api\Model\Collection\Rest\Attribute;
use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\Controller\AbstractController;
use Seahinet\Lib\Session\Csrf;
use Seahinet\Oauth\Model\Consumer;
use Zend\Crypt\PublicKey\{
    Rsa,
    RsaOptions,
    Rsa\PrivateKey
};

class RestController extends AbstractController
{

    use \Seahinet\Api\Traits\Rest;

    protected $authOptions = [];

    /**
     * {@inhertdoc}
     */
    public function dispatch($request = null, $routeMatch = null)
    {
        $response = $this->getResponse();
        if (!isset($_SERVER['HTTPS'])) {
            return $response->withStatus(403, 'SSL required');
        }
        if (empty($authorization = $request->getHeader('HTTP_AUTHORIZATION'))) {
            return $response->withStatus(401);
        } else {
            $parts = explode(' ', $authorization);
            if (!is_callable([$this, $method = 'authorize' . $parts[0]]) || !$this->$method($parts[1])) {
                return $response->withStatus(401);
            }
        }
        return parent::dispatch($request, $routeMatch);
    }

    /**
     * Authorize with oauth token
     * 
     * @param string $code
     * @return bool
     */
    public function authorizeOauth($code)
    {
        return $this->authorizeBearer($code);
    }

    /**
     * Authorize with bearer token
     * 
     * @param string $code
     * @return bool
     */
    public function authorizeBearer($code)
    {
        $this->authOptions = $this->getContainer()->get('cache')->fetch('$ACCESS_TOKEN$' . $code, 'OAUTH_');
        if ($this->authOptions) {
            $consumer = new Consumer;
            $consumer->load($this->authOptions['consumer_id']);
            if ($consumer->getId() && ($role = $consumer->getRole())) {
                $this->authOptions['role_id'] = $consumer['role_id'];
                $this->authOptions['validation'] = $role['validation'];
                return true;
            }
        }
        return false;
    }

    /**
     * Basic authentication
     * 
     * @param string $code
     * @return bool
     */
    public function authorizeBasic($code)
    {
        return false;
    }

    /**
     * Digest authentication
     * 
     * @param string $code
     * @return bool
     */
    public function authorizeDigest($code)
    {
        $data = [];
        foreach (preg_split('/\s*\,\s*/', $code) as $item) {
            $pos = strpos($item, '=');
            $data[substr($item, 0, strpos($item, '='))] = trim(substr($item, $pos + 1), ' \'"');
        }
        if (!isset($data['username'])) {
            return false;
        }
        $consumer = new Consumer;
        $consumer->load($data['username'], 'key');
        if ($consumer->getId()) {
            $crypt = new Rsa((new RsaOptions())->setOpensslPadding(OPENSSL_PKCS1_PADDING));
            $response = explode(':', $crypt->decrypt($data['response'], new PrivateKey($consumer->offsetGet('private_key'), $consumer->offsetGet('phrase')), Rsa::MODE_BASE64));
            unset($data['response']);
            ksort($data);
            $valid = implode(':', $data);
            if (count($response) === 2 && $response[2] === md5($response[0] . ':' . $response[1] . ':' . $valid . ':' . $consumer['key'])) {
                $user = $consumer->getRole()['validation'] === -1 ? (new User) : (new Customer);
                return $user->valid($data[0], $data[1]);
            }
        }
        return false;
    }

    public function authorizeCsrf($code)
    {
        if (base64_decode($code) === $this->getContainer()->get('session')->getId()) {
            $this->authOptions['type'] = 'CSRF';
            return true;
        }
        return false;
    }

    public function __call($name, $arguments)
    {
        if ($this->authOptions['type'] === 'CSRF') {
            return $this->getCsrfKey();
        }
        $method = $this->getRequest()->getMethod() . str_replace('_', '', substr($name, 0, -6));
        if (method_exists($this, $method)) {
            try {
                $response = $this->$method();
                return $response;
            } catch (Exception $e) {
                return $this->getResponse()->withStatus(400);
            }
        }
        return $this->getResponse()->withStatus(400);
    }

    protected function getAttributes($type, $isRead = true)
    {
        $attributes = new Attribute;
        $attributes->columns(['attributes'])
                ->where([
                    'operation' => $isRead ? 1 : 0,
                    'resource' => $type,
                    'role_id' => $this->authOptions['role_id']
        ]);
        return count($attributes) ? explode(',', $attributes[0]['attributes']) : [];
    }

    protected function getCsrfKey()
    {
        return (new Csrf)->getValue();
    }

}
