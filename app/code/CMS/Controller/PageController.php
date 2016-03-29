<?php

namespace Seahinet\CMS\Controller;

use Seahinet\Lib\Controller\ActionController;

class PageController extends ActionController
{

    public function indexAction()
    {
        $page = $this->getOption('page');
        $this->getResponse()->getBody()->write($page['content']);
    }

}
