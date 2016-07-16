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
        $root = $this->getLayout('cms_page');
        $root->addBodyClass('page-' . $page['uri_key']);
        $head = $root->getChild('head');
        $head->setTitle($page['title'])
                ->setKeywords($page['keywords'])
                ->setDescription($page['description']);
        $root->getChild('page', true)->setPageModel($page);
        return $root;
    }

}
