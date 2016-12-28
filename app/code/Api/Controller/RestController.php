<?php

namespace Seahinet\Api\Controller;

use Seahinet\Lib\Controller\ApiActionController;
use Seahinet\Api\Model\Collection\Rest\Attribute;

class RestController extends ApiActionController
{

    use \Seahinet\Api\Traits\Rest;

    public function __call($name, $arguments)
    {
        $method = $this->getRequest()->getMethod() . str_replace('_', '', substr($name, 0, -6));
        if (method_exists($this, $method)) {
            $response = $this->$method();
            return $response;
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

}
