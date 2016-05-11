<?php

namespace Seahinet\Resources\Route;

use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Route\Route;
use Seahinet\Lib\Route\RouteMatch;

class Router extends Route
{

    public function match(Request $request)
    {
        $uri = $request->getUri()->getPath();
        echo $uri;die();
    }

}
