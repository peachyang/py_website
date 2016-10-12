<?php

namespace Seahinet\Retailer\Controller;

use Seahinet\Lib\Controller\ActionController;


class ViewController extends ActionController
{

    public function indexAction()
    {
        $retailer = $this->getOption('retailer');
        $root = $this->getLayout('view_store');
        return $root;     

    }

    public function categoryAction()
    {
       echo "aa";
    }

    public function categoryAction()
    {
        
    }

}
