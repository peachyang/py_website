<?php

namespace Seahinet\Retailer\Controller;

use Seahinet\Lib\Controller\ActionController;


class ViewController extends ActionController
{

    public function indexAction()
    {
    	
        $root = $this->getLayout('view_store');
        $root->getChild('main', true)->setVariable('store_id', $this->getOption('store_id'));
        $root->getChild('main', true)->setVariable('retailer', $this->getOption('retailer'));
        return $root;     

    }

    public function categoryAction()
    {
     
    }


}
