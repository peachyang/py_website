<?php

namespace Seahinet\Catalog\Route;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Route\Route;
use Seahinet\Lib\Route\RouteMatch;

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
        if ($result = $this->getContainer()->get('indexer')->select('catalog_url', Bootstrap::getLanguage()->getId(), ['path' => $path])) {
            if ($result[0]['product_id']) {
                return new RouteMatch([
                    'controller' => 'Seahinet\\Catalog\\Controller\\ProductController',
                    'action' => 'index',
                    'product_id' => $result[0]['product_id'],
                    'category_id' => $result[0]['category_id']
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
