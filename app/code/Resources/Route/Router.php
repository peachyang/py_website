<?php

namespace Seahinet\Resources\Route;

use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Route\Route;
use Seahinet\Lib\Route\RouteMatch;

class Router extends Route
{

    public function match(Request $request)
    {
        $path = $request->getUri()->getPath();
        if (preg_match('#^/pub/resource/resized/(?P<width>\d+)x(?P<height>\d*)/(?P<file>.+\.(?:jpe?g|gif|png))$#', $path, $matches)) {
            return new RouteMatch($matches + ['controller' => '\\Seahinet\\Resources\\Controller\\ResizeController'], $request);
        }
        return false;
    }

}
