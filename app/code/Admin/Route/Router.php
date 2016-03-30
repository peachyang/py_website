<?php

namespace Seahinet\Admin\Route;

use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Route\Route;
use Seahinet\Lib\Route\RouteMatch;

class Router extends Route
{

    public function match(Request $request)
    {
        $path = trim($request->getUri()->getPath(), '/');
        $parts = explode('/', $path);
        if ($parts[0] === 'admin') {
            $options = ['namespace' => 'Seahinet\\Admin\\Controller'];
            if (isset($parts[1])) {
                $options['controller'] = str_replace('_', '\\', $parts[1]) . 'Controller';
            }
            if (isset($parts[2])) {
                $options['action'] = $parts[2];
            }
            return new RouteMatch($options, $request);
        }
        return false;
    }

}
