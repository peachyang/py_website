<?php

namespace Seahinet\Lib\Controller;

use Seahinet\Oauth\Model\Consumer;

abstract class ApiActionController extends AbstractController
{

    protected $authOptions = [];

    public function dispatch($request = null, $routeMatch = null)
    {
        if (!isset($_SERVER['HTTPS'])) {
            return $this->getResponse()->withStatus(403, 'SSL required');
        }
        if (empty($authorization = $request->getHeader('HTTP_AUTHORIZATION'))) {
            return $this->getResponse()->withStatus(401);
        } else {
            $parts = explode(' ', $authorization);
            if (is_callable([$this, $method = 'authorize' . $parts[0]])) {
                if (!$this->$method($parts[1])) {
                    return $this->getResponse()->withStatus(401);
                }
            } else {
                return $this->getResponse()->withStatus(400);
            }
        }
        return parent::dispatch($request, $routeMatch);
    }

    public function authorizeOauth($code)
    {
        $this->authOptions = $this->getContainer()->get('cache')->fetch('$ACCESS_TOKEN$' . $code, 'OAUTH_');
        if ($this->authOptions) {
            $consumer = new Consumer;
            $consumer->load($this->authOptions['consumer_id']);
            $this->authOptions['role_id'] = $consumer['role_id'];
            return true;
        }
        return false;
    }

}
