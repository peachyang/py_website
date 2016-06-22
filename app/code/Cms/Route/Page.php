<?php

namespace Seahinet\Cms\Route;

use Seahinet\Cms\Model\Collection\Page as Collection;
use Seahinet\Cms\Model;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Route\Route;
use Seahinet\Lib\Route\RouteMatch;

class Page extends Route
{

    public function match(Request $request)
    {
        $path = trim($request->getUri()->getPath(), '/');
        $languageId = Bootstrap::getLanguage()->getId();
        $collection = new Collection;
        $collection->join('cms_page_language', 'cms_page.id=cms_page_language.page_id', ['page_id'], 'left');
        if ($path === '') {
            $collection->where(['uri_key' => 'home', 'language_id' => $languageId, 'status' => 1]);
            if (count($collection)) {
                return new RouteMatch(['page' => new Model\Page($collection[0]), 'namespace' => 'Seahinet\\Cms\\Controller', 'controller' => 'PageController', 'action' => 'index'], $request);
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
        $collection->reset('where')
                ->where(['uri_key' => $part, 'language_id' => $languageId, 'status' => 1]);
        if (count($collection)) {
            $page = new Model\Page;
            $page->load($collection[0]['id']);
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
