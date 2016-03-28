<?php

namespace Seahinet\Lib\Route;

use FastRoute\RouteCollector;

class Collector extends RouteCollector
{

    /**
     * @param array|string $httpMethod
     * @param string $route
     * @param string $handler
     */
    public function addRoute($httpMethod, $route, $handler)
    {
        if (class_exists($route)) {
            $route = new $route;
            foreach ((array) $httpMethod as $method) {
                $this->dataGenerator->addRoute($method, $route, $handler);
            }
        } else {
            parent::addRoute($httpMethod, $route, $handler);
        }
    }

}
