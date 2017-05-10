<?php

namespace Seahinet\Cms\Route;

use Seahinet\Cms\Model\Category;
use Seahinet\Cms\Model\Page as Model;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Route\Route;
use Seahinet\Lib\Route\RouteMatch;

class Page extends Route
{

    use \Seahinet\Lib\Traits\Container;

    public function match(Request $request)
    {
        $path = trim($request->getUri()->getPath(), '/');
        if (substr($path, -5) === '.html') {
            $path = substr($path, 0, -5);
        } else if (substr($path, -4) === '.htm') {
            $path = substr($path, 0, -4);
        } else if ($path === '') {
            $path = 'home';
        } else {
            return false;
        }
        $config = $this->getContainer()->get('config');
        if ($path && Bootstrap::isMobile() && $config['theme/global/layout'] !== $config['theme/global/mobile_layout']) {
            $path .= '-' . $config['theme/global/mobile_layout'];
        }
        if ($path && ($prefix = $this->getContainer()->get('config')['route']['default']['prefix'] ?? '')) {
            $path = $prefix . $path;
        }
        if ($result = $this->getContainer()->get('indexer')->select('cms_url', Bootstrap::getLanguage()->getId(), ['path' => $path])) {
            if ($result[0]['page_id']) {
                return new RouteMatch([
                    'page' => (new Model)->load($result[0]['page_id']),
                    'category' => isset($result[0]['category_id']) ? (new Category)->load($result[0]['category_id']) : null,
                    'namespace' => 'Seahinet\\Cms\\Controller',
                    'controller' => 'PageController',
                    'action' => 'index'
                        ], $request);
            } else {
                return false;
            }
        }
        return false;
    }

}
