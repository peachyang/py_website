<?php

namespace Seahinet\Lib\Controller;

/**
 * Controller with authorization for backend pages
 */
class AuthActionController extends ActionController
{

    public function dispatch($request = null, $routeMatch = null)
    {
        return parent::dispatch($request, $routeMatch);
    }

}
