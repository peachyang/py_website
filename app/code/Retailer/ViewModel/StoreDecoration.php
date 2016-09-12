<?php

namespace Seahinet\Retailer\ViewModel;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Retailer\Model\StoreTemplate;
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
    public function getTemplateView()
    {
		
		$id = $this->getQuery('id');
		$template = new StoreTemplate();
		$templateView = $template->load($id);
		return $templateView;				 		      
    }


    
}
    