<?php

namespace Seahinet\CMS\Controller;

use Seahinet\Lib\Controller\ActionController;

class PageController extends ActionController
{

    public function indexAction()
    {
        $page = $this->getOption('page');
        return $page['content'];
    }

}
