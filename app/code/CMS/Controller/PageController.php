<?php

namespace Seahinet\CMS\Controller;

use Seahinet\Lib\Controller\ActionController;

class PageController extends ActionController
{

    public function indexAction()
    {
        $page = $this->getOption('page');
        $layout = $this->getLayout('cms_page', true);
        $layout->getChild('content')->setPageModel($page);
        return $layout;
    }

}
