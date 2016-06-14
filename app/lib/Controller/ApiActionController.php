<?php

namespace Seahinet\Lib\Controller;

abstract class ApiActionController extends ActionController
{

    public function dispatch($request = null, $routeMatch = null)
    {
        if (!isset($_SERVER['HTTPS'])) {
            return $this->getResponse()->withStatus(403, 'SSL required');
        }
        return parent::dispatch($request, $routeMatch);
    }

}
