<?php

namespace Seahinet\Retailer\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;

class ViewController extends ActionController
{

    public function indexAction()
    {
        $retailer = $this->getOption('retailer');
        $root = $this->getLayout('view_store');
        $root->getChild('main', true)->setVariable('store_id', $this->getOption('store_id'));
        $root->getChild('main', true)->setVariable('retailer', $retailer);
        $root->getChild('main', true)->setVariable('key', $retailer);
        $segment = new Segment('core');
        $segment->set('store', $retailer->getStore()->offsetGet('code'));
        return $root;
    }

    public function categoryAction()
    {
        
    }

}
