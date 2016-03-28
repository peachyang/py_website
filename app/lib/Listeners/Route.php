<?php

namespace Seahinet\Lib\Listeners;

use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Traits;
use FastRoute\cachedDispatcher;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

class Route implements ListenerInterface
{

    use Traits\Container;

    public function dispatch()
    {
        $routers = $this->getContainer()->get('config')['route'];
        $dispatcher = cachedDispatcher(function(RouteCollector $collector) use ($routers) {
            foreach ($routers as $router) {
                if (isset($router['regex'])) {
                    $collector->addRoute((isset($router['method']) ? $router['method'] : ['GET', 'POST']), $router['regex'], isset($router['controller']) ? $router['controller'] : $routers['default']['controller']);
                }
            }
        }, ['cacheFile' => BP . 'var/cache/route/']);
        $request = new Request();
        $uri = $request->getUri()->__toString();
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $routeInfo = $dispatcher->dispatch($request->getMethod(), rawurldecode($uri));
        $className = $routers['default']['controller'];
        if ($routeInfo[0] == Dispatcher::FOUND && class_exists($routeInfo[1])) {
            $className = $routeInfo[1];
        }
        $controller = new $className();
        return $controller->dispatch($routeInfo, $request);
    }

}
