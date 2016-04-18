<?php

namespace Seahinet\Cms\Controller;

use Seahinet\Lib\Controller\ActionController;

class PageController extends ActionController
{

    public function indexAction()
    {
        $page = $this->getOption('page');
        $layout = $this->getLayout('cms_page', true);
        $layout->getChild('page', true)->setPageModel($page);
        var_dump($layout);
        return $layout;
    }

}
