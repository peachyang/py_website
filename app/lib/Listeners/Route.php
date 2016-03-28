<?php

namespace Seahinet\Lib\Listeners;

use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Traits;
use FastRoute\cachedDispatcher;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

/**
 * Listen route_dispatch event
 */
class Route implements ListenerInterface
{

    use Traits\Container;

    public function dispatch()
    {
        $routers = $this->getContainer()->get('config')['route'];
        $dispatcher = cachedDispatcher(function(RouteCollector $collector) use ($routers) {
            foreach ($routers as $router) {
                if (isset($router['route'])) {
                    $collector->addRoute((isset($router['method']) ? $router['method'] : ['GET', 'POST']), $router['route'], isset($router['controller']) ? $router['controller'] : $routers['default']['controller'], isset($router['priority']) ? $router['priority'] : 0);
                }
            }
        }, ['cacheFile' => BP . 'var/cache/route/', 'routeCollector' => 'Seahinet\\Lib\\Route\\Collector', 'dataGenerator' => 'Seahinet\\Lib\\Route\\Generator', 'dispatcher' => 'Seahinet\\Lib\Route\\Dispatcher']);
        $request = new Request();
        $routeMatch = $dispatcher->dispatch($request);
        if ($routeMatch) {
            
        }
        $className = $routers['default']['controller'];
        $controller = new $className();
        return $controller->dispatch($request);
    }

}
