<?php

namespace Seahinet\Lib\Route;

use Seahinet\Lib\Http\Request;
use Serializable;

interface RouteInterface extends Serializable
{

    /**
     * Match request
     * 
     * @param Request $request
     * @return RouteMatch|false when dismatch the request
     */
    public function match(Request $request);
}
