<?php

namespace Seahinet\Lib\Listeners;

use Seahinet\Lib\Route\Dispatcher;
use Seahinet\Lib\Route\Collector;
use Seahinet\Lib\Route\Generator;
use FastRoute\RouteParser\Std;

/**
 * Listen route event
 */
class Route implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    /**
     * Add routers to dispatcher
     * 
     * @param array $routers
     * @return Dispatcher
     */
    protected function getDispatcher($routers)
    {
        $cache = $this->getContainer()->get('cache');
        $data = $cache->fetch('ROUTE_CACHE');
        if (!$data) {
            $collector = new Collector(new Std, new Generator);
            foreach ($routers as $router) {
                if (isset($router['route'])) {
                    $collector->addRoute(
                            ($router['method'] ?? ['GET', 'POST']), $router['route'], ($router['controller'] ?? ($router['namespace'] ??
                                            $routers['default']['controller'])), ($router['priority'] ?? 0)
                    );
                }
            }
            $data = $collector->getData();
            $cache->save('ROUTE_CACHE', $data);
        }
        return new Dispatcher($data);
    }

    public function dispatch($event)
    {
        $routers = $event['routers'];
        $dispatcher = $this->getDispatcher($routers);
        $request = $this->getContainer()->get('request');
        $routeMatch = $dispatcher->dispatch($request);
        if (!$routeMatch) {
            $routeMatch = $routers['default'];
        }
        if (isset($routeMatch['namespace'])) {
            $className = $routeMatch['namespace'] . '\\' .
                    (isset($routeMatch['controller']) ?
                    (strpos($routeMatch['controller'], 'Controller') ?
                    $routeMatch['controller'] :
                    str_replace(' ', '\\', ucwords(str_replace('_', ' ', $routeMatch['controller']))) . 'Controller'
                    ) :
                    'IndexController');
        } else {
            $className = $routeMatch['controller'] ?? 'IndexController';
        }
        if (!class_exists($className)) {
            $routeMatch = $routers['default'];
            $className = $routeMatch['controller'];
            if (!class_exists($className)) {
                header('HTTP/1.1 404 Not Found');
                exit;
            }
        }
        $controller = new $className;
        $this->getContainer()->get('response')->setData($controller->dispatch($request, $routeMatch));
    }

}
