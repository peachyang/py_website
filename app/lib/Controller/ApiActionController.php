<?php

namespace Seahinet\Lib\Controller;

abstract class ApiActionController extends AbstractController
{

    public function dispatch(\Seahinet\Lib\Http\Request $request = null, $routeMatch = null)
    {
        if ($_SERVER['SCRIPT_NAME'] !== '/api.php') {
            return $this->getResponse()->withStatus(400);
        }
        return parent::dispatch($request, $routeMatch);
    }

}
