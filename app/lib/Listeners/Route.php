<?php

namespace Seahinet\Lib\Listeners;

use Seahinet\Lib\Http\Request;
use FastRoute\RouteCollector;

/**
 * Listen route_dispatch event
 */
class Route implements ListenerInterface
{

    public function dispatch($event, $event_name, $eventDispatcher)
    {
        $routers = $event['routers'];
        $cache = BP . 'var/cache/';
        if (!is_dir($cache)) {
            mkdir($cache, 0744, true);
        }
        $dispatcher = \FastRoute\cachedDispatcher(function(RouteCollector $collector) use ($routers) {
            foreach ($routers as $router) {
                if (isset($router['route'])) {
                    $collector->addRoute((isset($router['method']) ? $router['method'] : ['GET', 'POST']), $router['route'], isset($router['controller']) ? $router['controller'] : $routers['default']['controller'], isset($router['priority']) ? $router['priority'] : 0);
                }
            }
        }, ['cacheFile' => $cache . 'route', 'routeCollector' => 'Seahinet\\Lib\\Route\\Collector', 'dataGenerator' => 'Seahinet\\Lib\\Route\\Generator', 'dispatcher' => 'Seahinet\\Lib\Route\\Dispatcher']);
        $request = new Request();
        $routeMatch = $dispatcher->dispatch($request);
        if (!$routeMatch) {
            $routeMatch = $routers['default'];
        }
        $controller = new $routeMatch['controller']();
        $eventDispatcher->trigger('render', ['response' => $controller->dispatch($request, $routeMatch)]);
    }

}
