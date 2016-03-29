<?php

namespace Seahinet\Lib\Route;

use Seahinet\Lib\Http\Request;
use FastRoute\Dispatcher\GroupCountBased;

class Dispatcher extends GroupCountBased
{

    /**
     * @var array 
     */
    protected $objectRoutes = [];

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        list($this->staticRouteMap, $this->variableRouteData, $this->objectRoutes) = $data;
    }

    /**
     * @param Request $request
     * @return RouteMatch
     */
    public function dispatch($request, $uri = null)
    {
        $result = parent::dispatch($request->getMethod(), (string) $request->getUri());
        if ($result[0] === static::FOUND) {
            return new RouteMatch(['controller' => $result[1], 'action' => $result[2]['action']], $request);
        } else {
            foreach ($this->objectRoutes as $route) {
                $result = $route['object']->match($request);
                if ($result !== false) {
                    return $result;
                }
            }
        }
        return false;
    }

}
