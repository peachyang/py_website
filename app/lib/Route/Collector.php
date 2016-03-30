<?php

namespace Seahinet\Lib\Route;

use FastRoute\RouteParser;
use FastRoute\DataGenerator;
use FastRoute\RouteCollector;

class Collector extends RouteCollector
{

    private $dataGenerator;

    public function __construct(RouteParser $routeParser, DataGenerator $dataGenerator)
    {
        $this->dataGenerator = $dataGenerator;
        parent::__construct($routeParser, $dataGenerator);
    }

    /**
     * @param array|string $httpMethod
     * @param string $route
     * @param string $handler
     */
    public function addRoute($httpMethod, $route, $handler, $priority = 0)
    {
        if (class_exists($route)) {
            $route = new $route;
            $this->dataGenerator->addRoute('get', $route, $handler, $priority);
        } else {
            parent::addRoute($httpMethod, $route, $handler);
        }
    }

}
