<?php

namespace Seahinet\Retailer\Controller;

use Seahinet\Lib\Controller\AbstractController;

class ViewController extends AbstractController
{

    public function indexAction()
    {
        $retailer = $this->getOption('retailer');
    }

    public function categoryAction()
    {
        
    }

}
