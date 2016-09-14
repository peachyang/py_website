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
    public function getTemplateView($model = 0)
    {
		
		$id = $this->getQuery('id');
		$segment = new Segment('customer');	
		if(!empty($id)){		
			$template = new StoreTemplate();
			$templateView = $template->load($id);
		}else{
			$template = new StoreTemplateCollection();
			$templateViewCollection = $template->storeTemplateList($segment->get('customer')['store_id'],1);
			if(count($templateViewCollection))
				$templateView = $templateViewCollection[0];
		}
			
		if( $templateView['store_id'] != $segment->get('customer')['store_id'])
			$templateView = [];
		
		if(!empty($templateView))
			$templateView = $this->changeModel($templateView,$model);
		
		return $templateView;				 		      
    }
	
	public function changeModel($view,$model){
       return $view;
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
    