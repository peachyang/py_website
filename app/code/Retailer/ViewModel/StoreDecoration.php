<?php

namespace Seahinet\Retailer\ViewModel;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Lib\Session\Segment;
use Zend\Db\Sql\Expression;

class StoreDecoration extends Template
{
    /**  
    * Get customers
    * 
    * @access public 
    * @return object 
    */ 
    public function getCustomers()
    {
       $segment = new Segment('customer');
        
	   return $segment->get('customer')['username'];
 		      

    }


    
}
    