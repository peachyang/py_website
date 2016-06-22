<?php

namespace Seahinet\Lib\Controller;

use Seahinet\Oauth\Model\Consumer;

/**
 * Controller for api request
 */
abstract class ApiActionController extends AbstractController
{

    protected $authOptions = [];

    /**
     * {@inhertdoc}
     */
    public function dispatch($request = null, $routeMatch = null)
    {
        if (!isset($_SERVER['HTTPS'])) {
            return $this->getResponse()->withStatus(403, 'SSL required');
        }
        if (empty($authorization = $request->getHeader('HTTP_AUTHORIZATION'))) {
            return $this->getResponse()->withStatus(401);
        } else {
            $parts = explode(' ', $authorization);
            if (!is_callable([$this, $method = 'authorize' . $parts[0]]) || !$this->$method($parts[1])) {
                return $this->getResponse()->withStatus(401);
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
            $this->authOptions['role_id'] = $consumer['role_id'];
            return true;
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
        return false;
    }

}
