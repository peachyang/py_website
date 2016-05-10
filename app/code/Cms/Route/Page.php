<?php

namespace Seahinet\Cms\Route;

use Seahinet\Cms\Model;
use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Route\Route;
use Seahinet\Lib\Route\RouteMatch;

class Page extends Route
{

    public function match(Request $request)
    {
        $path = trim($request->getUri()->getPath(), '/');
        if ($path === '') {
            $home = new Model\Page;
            $home->load('home', 'uri_key');
            if ($home->getId() && $home['status']) {
                return new RouteMatch(['page' => $home, 'namespace' => 'Seahinet\\Cms\\Controller', 'controller' => 'PageController', 'action' => 'index'], $request);
            } else {
                return false;
            }
        }
        if (substr($path, -5) === '.html') {
            $path = substr($path, 0, -5);
        } else if (substr($path, -4) === '.htm') {
            $path = substr($path, 0, -4);
        } else {
            return false;
        }
        $parts = explode('/', $path);
        $stack = [];
        $part = array_pop($parts);
        $page = new Model\Page;
        $page->load($part, 'uri_key');
        if ($page->getId() && $page['status']) {
            while ($part = array_pop($parts)) {
                $model = new Model\Category;
                $model->load($part, 'uri_key');
                if ($model->getId() && $model['status']) {
                    $stack[] = $model;
                }
            }
        } else {
            return false;
        }
        if (empty($stack) || !in_array($stack[0]->getId(), $page['category_id'])) {
            return false;
        }
        for ($i = 0; $i < count($stack); $i++) {
            if (isset($stack[$i + 1])) {
                if ($stack[$i]['parent_id'] != $stack[$i + 1]['id']) {
                    return false;
                }
            } else if (is_numeric($stack[$i]['parent_id'])) {
                return false;
            }
        }
        return new RouteMatch(['page' => $page, 'namespace' => 'Seahinet\\Cms\\Controller', 'controller' => 'PageController', 'action' => 'index'], $request);
    }

}
