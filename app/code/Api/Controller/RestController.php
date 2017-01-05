<?php

namespace Seahinet\Api\Controller;

use Exception;
use Seahinet\Api\Model\Collection\Rest\Attribute;
use Seahinet\Lib\Controller\ApiActionController;
use Seahinet\Lib\Session\Csrf;

class RestController extends ApiActionController
{

    use \Seahinet\Api\Traits\Rest;

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
