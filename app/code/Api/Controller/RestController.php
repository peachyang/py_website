<?php

namespace Seahinet\Api\Controller;

use Seahinet\Lib\Controller\ApiActionController;
use Seahinet\Lib\Model\Collection\Eav\Attribute;

class RestController extends ApiActionController
{

    use \Seahinet\Api\Traits\Rest;

    public function __call($name, $arguments)
    {
        $method = $this->getRequest()->getMethod() . substr($name, 0, -6);
        if (method_exists($this, $method)) {
            $response = $this->$method();
            return $response;
        }
        return $this->getResponse()->withStatus(400);
    }

    protected function getAttributes($type, $isRead = true)
    {
        $attributes = new Attribute;
        $attributes->columns(['code'])
                ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                ->join('api_rest_attribute', 'api_rest_attribute.attribute_id=eav_attribute.id', [], 'left')
                ->join('api_rest_role', 'api_rest_role.id=api_rest_attribute.role_id', [], 'left')
                ->where([
                    ($isRead ? 'readable' : 'writeable') => 1,
                    'role_id' => $this->authOptions['role_id'],
                    'eav_entity_type.code' => $type
        ]);
        return $attributes;
    }

}
