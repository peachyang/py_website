<?php

namespace Seahinet\Retailer\Route;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Route\Route;
use Seahinet\Lib\Route\RouteMatch;
use Seahinet\Retailer\Model\Retailer;

class Router extends Route
{

    use \Seahinet\Lib\Traits\Container;

    public function match(Request $request)
    {
        $path = trim($request->getUri()->getPath(), '/');
        if (substr($path, -5) === '.html') {
            $path = substr($path, 0, -5);
        } else if (substr($path, -4) === '.htm') {
            $path = substr($path, 0, -4);
        } else {
            return false;
        }
        $parts = explode('/', $path);
        if (count($parts) <= 1 && $parts[0] !== 'store') {
            return false;
        }
        $retailer = new Retaler;
        $retailer->load(rawurldecode($parts[1]), 'uri_key');
        if ($retailer->getId()) {
            if (count($parts) === 2) {
                return new RouteMatch([
                    'controller' => 'Seahinet\\Retailer\\Controller\\ViewController',
                    'action' => 'index',
                    'store_id' => $retailer->getStore()->getId(),
                    'retailer' => $retailer
                        ], $request);
            } else {
                
                return new RouteMatch([
                    'controller' => 'Seahinet\\Catalog\\Controller\\CategoryController',
                    'action' => 'index',
                    'category_id' => $result[0]['category_id']
                        ], $request);
            }
        }
        return false;
    }

}
