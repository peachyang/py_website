<?php

namespace Seahinet\Retailer\Controller;

use Exception;
use Seahinet\Retailer\Model\Retailer as Rmodel;
use Seahinet\Lib\Session\Segment;

/** 
* Retailer submenu goods marketing controller
* 
*/  
class MarketingController extends AuthActionController
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
    * releaseAction  
    * Show release good view
    * 
    * @access public 
    * @return object 
    */
    public function activityAction()
    {
        $root = $this->getLayout('retailer_activity');
        return $root;
    }
    
    /** 
    * viewActivityAction  
    * Show more information about activity
    * 
    * @access public 
    * @return object 
    */
    public function viewActivityAction()
    {
        $root = $this->getLayout('retailer_activity_view');
        return $root;
    }

    /** 
    * promotionAction  
    * Manage the retailer's promotion
    * 
    * @access public 
    * @return object 
    */
    public function promotionAction()
    {
        $root = $this->getLayout('retailer_promotion');
        return $root;
    }
    
    /** 
    * viewPromotionAction  
    * Show detail information about promotion
    * 
    * @access public 
    * @return object 
    */
    public function viewPromotionAction()
    {
        $root = $this->getLayout('retailer_promotion_view');
        return $root;
    }

}
