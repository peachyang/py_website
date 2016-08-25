<?php

namespace Seahinet\Retailer\Controller;

use Exception;
use Seahinet\Retailer\Model\Retailer as Rmodel;
use Seahinet\Customer\Model\Customer as Cmodel;
use Seahinet\Lib\Session\Segment;

/** 
* Retailer submenu store management controller
* 
*/  
class StoreController extends AuthActionController
{

    public function indexAction()
    {
        $segment = new Segment('customer');
        
        if ($customerId = $segment->get('customer')->getId()) {
            $customer = new Cmodel;
            $customer->load($customerId);
            $root = $this->getLayout('retailer_store_settings');
            $root->getChild('main', true)->setVariable('customer', $customer);
            return $root;
        }
        return $root;
    }

    /** 
    * settingsAction  
    * Show release good view
    * 
    * @access public 
    * @return object 
    */
    public function settingsAction()
    {
        $root = $this->getLayout('retailer_store_settings');
        return $root;
    }
    
    /** 
    * cotegoryAction  
    * Show category management view
    * 
    * @access public 
    * @return object 
    */
    public function cotegoryAction()
    {
        $root = $this->getLayout('retailer_store_category');
        return $root;
    }
    
    /** 
    * photoAction  
    * Show photo management view
    * @access public 
    * @return object 
    */
    public function photoAction()
    {
        $root = $this->getLayout('retailer_store_photo');
        return $root;
    }

}
