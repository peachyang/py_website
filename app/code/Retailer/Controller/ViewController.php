<?php

namespace Seahinet\Retailer\Controller;

use Seahinet\Lib\Controller\ActionController;


class ViewController extends ActionController
{

    public function indexAction()
    {
        $retailer = $this->getOption('retailer');
        //echo $this->getOption('store_id');
        $root = $this->getLayout('view_store');
        $root->getChild('main', true)->setVariable('store_id', $this->getOption('store_id'));
        return $root;     

    }

    public function categoryAction()
    {
     
    }


}
