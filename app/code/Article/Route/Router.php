<?php

namespace Seahinet\Article\Route;

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
        $isJson = false;
        if (substr($path, -5) === '.html') {
            $path = substr($path, 0, -5);
        } else if (substr($path, -4) === '.htm') {
            $path = substr($path, 0, -4);
        } else if (substr($path, -5) === '.json') {
            $path = substr($path, 0, -5);
            $isJson = true;
        } else {
            return false;
        }
        if ($isJson && $path === 'article/nav') {
            return new RouteMatch([
                    'controller' => 'Seahinet\\Article\\Controller\\CategoryController',
                    'action' => 'nav',
                    'is_json' => $isJson
                        ], $request);
        }
        if ($result = $this->getContainer()->get('indexer')->select('article_url', Bootstrap::getLanguage()->getId(), ['path' => rawurldecode($path)])) {
            if ($result[0]['article_id']) {
                return new RouteMatch([
                    'controller' => 'Seahinet\\Article\\Controller\\ProductController',
                    'action' => 'index',
                    'article_id' => $result[0]['article_id'],
                    'category_id' => $result[0]['category_id'],
                    'is_json' => $isJson
                        ], $request);
            } else {
                return new RouteMatch([
                    'controller' => 'Seahinet\\Article\\Controller\\CategoryController',
                    'action' => 'index',
                    'category_id' => $result[0]['category_id'],
                    'is_json' => $isJson
                        ], $request);
            }
        }
        return false;
    }

}
