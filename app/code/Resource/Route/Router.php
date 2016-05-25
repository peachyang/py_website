<?php

namespace Seahinet\Resource\Route;

use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Route\Route;
use Seahinet\Lib\Route\RouteMatch;

class Router extends Route
{

    public function match(Request $request)
    {
        $path = $request->getUri()->getPath();
        if (preg_match('#^/pub/resource/image/resized/(?P<width>\d+)x(?P<height>\d*)/(?P<file>.+\.(?:jpe?g|gif|png|wbmp|xbm))$#', $path, $matches)) {
            return new RouteMatch($matches + ['controller' => '\\Seahinet\\Resource\\Controller\\ResizeController'], $request);
        }
        return false;
    }

}
