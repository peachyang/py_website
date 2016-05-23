<?php

namespace Seahinet\Lib\Route;

use Seahinet\Lib\Http\Request;
use FastRoute\Dispatcher\GroupCountBased;

/**
 * Dispatch routers
 * 
 * @todo Adjust other route methods
 */
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
    	$path = $request->getUri()->getPath();
        $result = parent::dispatch($request->getMethod(), substr($path,0,1)==='/' ? $path : '/' . $path);
        if ($result[0] === static::FOUND) {
            return new RouteMatch(array_merge(['controller' => $result[1]], $result[2]), $request);
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
