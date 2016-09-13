<?php

namespace Seahinet\Retailer\Controller;

use Exception;
use Seahinet\Retailer\Model\Retailer as Rmodel;
use Seahinet\Retailer\Model\StoreTemplate;
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
    
    /** 
    * brandAction  
    * Show brand management view
    * @access public 
    * @return object 
    */
    public function brandAction()
    {
        $root = $this->getLayout('retailer_brand');
        return $root;
    }
    
     /** 
    * viewAction  
    * Show retailer store's home page
    * @access public 
    * @return object 
    */
    public function viewAction()
    {
        $root = $this->getLayout('view_store');
        return $root;
    }
	
	/**
	 * temporary view
	 * 
	 */
	
	public function view1Action()
    {
        $root = $this->getLayout('view_store1');
        return $root;
    }
	
	/** 
    * decorationAction  
    * decorate store page
    * @access public 
    * @return object 
    */
    public function decorationAction()
    {
        $root = $this->getLayout('decoration_store');
        return $root;
    }
	
	
	public function decorationListAction(){
		$root = $this->getLayout('decoration_list');
		$root->getChild('main', true)->setVariable('subtitle', 'Sales of Goods');
        return $root;
	}
	
	/** 
    * addTemplateAction 
    * return json for store decoration page
    * @access public 
    * @return object 
    */
	
	public function addTemplateAction()
	{
		$data = $this->getRequest()->getPost();
		$segment = new Segment('customer');
		$data['store_id'] = $segment->get('customer')['store_id'];
		$model = new StoreTemplate();
        
        if($data['template_id']=='0')
		{
        	$model->setData($data);	
			$model->save();
			$template_id = $model->getID();
		}
		else{
			$template_id = $data['template_id'];
			unset($data['template_id']);
//			$model->update($data,['id'=>$template_id]);
			$model->load($template_id);
			$model->setData($data);
			$model->save();
		}

		$result = ['status'=>true,'id'=>$template_id];	
		echo json_encode($result);
	}
	
	
	

}
