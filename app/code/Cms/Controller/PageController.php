<?php

namespace Seahinet\Cms\Controller;

use Seahinet\Lib\Controller\ActionController;

class PageController extends ActionController
{

    public function indexAction()
    {
        $page = $this->getOption('page');
        if (!$page) {
            return $this->notFoundAction();
        }
        $category = $this->getOption('category');
        $root = $this->getLayout($category && $category->offsetGet('show_navigation') ? 'cms_page_with_nav' : 'cms_page');
        $root->addBodyClass('page-' . $page['uri_key']);
        $head = $root->getChild('head');
        $head->setTitle($page['title'])
                ->setKeywords($page['keywords'])
                ->setDescription($page['description']);
        $navigation = $root->getChild('navigation', true);
        if ($navigation) {
            $navigation->setVariables([
                'page' => $page,
                'category' => $category
            ]);
        }
        $root->getChild('page', true)->setPageModel($page);
        return $root;
    }

    public function orderStatusAction()
    {
        return $this->getLayout('cms_help_order_status');
    }

}
