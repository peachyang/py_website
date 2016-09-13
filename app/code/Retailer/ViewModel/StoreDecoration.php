<?php

namespace Seahinet\Retailer\ViewModel;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Retailer\Model\StoreTemplate;
use Seahinet\Retailer\Model\Collection\StoreTemplateCollection;
use Seahinet\Lib\Session\Segment;
use Zend\Db\Sql\Expression;

class StoreDecoration extends Template
{
    /**  
    * Get store template view
    * 
    * @access public 
    * @return object 
    */ 
    public function getTemplateView()
    {
		
		$id = $this->getQuery('id');
		$template = new StoreTemplate();
		$templateView = $template->load($id);
		$segment = new Segment('customer');
		if( $templateView['store_id'] != $segment->get('customer')['store_id'])
		$templateView = [];
		return $templateView;				 		      
    }
	
	/**  
    * Get store template list
    * 
    * @access public 
    * @return object 
    */ 
	public function getTemplateList(){
		$segment = new Segment('customer');
		$template = new StoreTemplateCollection();
		$template->storeTemplateList($segment->get('customer')['store_id']);
		return $template;
		
	}


    
}
    