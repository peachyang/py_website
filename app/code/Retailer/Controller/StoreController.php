<?php

namespace Seahinet\Retailer\Controller;

use Exception;
use Seahinet\Retailer\Model\Retailer as Rmodel;
use Seahinet\Retailer\Model\StoreTemplate;
use Seahinet\Retailer\Model\StorePicinfo;
use Seahinet\Retailer\Model\Collection\StoreTemplateCollection;
use Seahinet\Retailer\Model\Collection\StorePicInfoCollectionCollection;
use Seahinet\Resource\Model\Collection\Category;
use Seahinet\Customer\Model\Customer as Cmodel;
use Seahinet\Resource\Model\Resource as Model;
use Seahinet\Lib\Session\Segment;
use Seahinet\Retailer\ViewModel\StoreDecoration as SDViewModel;

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
     * Show release product view
     * 
     * @access public 
     * @return object 
     */
    public function settingAction()
    {
        $root = $this->getLayout('retailer_store_setting');
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
		$store_id = $data['store_id'];
		$data['store_id'] = $segment->get('customer')['store_id'];
		$model = new StoreTemplate();
        
        if($data['template_id']=='0' || $store_id == '0' )
		{
        	$model->setData($data);	
			$model->save();
			$template_id = $model->getID();
		}
		else{
			$template_id = $data['template_id'];
			unset($data['template_id']);
			$model->load($template_id);
			$model->setData($data);
			$model->save();
		}

		$result = ['status'=>true,'id'=>$template_id,'store_id'=>$data['store_id']];	
		echo json_encode($result);
	}
	
	public function delTemplateAction(){
		$data = $this->getRequest()->getPost();
		$segment = new Segment('customer');
		$store_id = $segment->get('customer')['store_id'];
		
		$model = new StoreTemplate();
		$model->load($data['id']);
		if($model['store_id']!=$store_id)
			$result = ['status'=>FALSE];
		else{
			$model->remove();
			$result = ['status'=>TRUE];
		}
		
		echo json_encode($result);
		
	}
	
	public function setTemplateAction(){
		$data = $this->getRequest()->getPost();
		$segment = new Segment('customer');
		$store_id = $segment->get('customer')['store_id'];
		
		$model = new StoreTemplate();
		$model->load($data['id']);
		if($model['store_id']!=$store_id)
			$result = ['status'=>FALSE];
		else{
		    $template = new StoreTemplateCollection();
		    $template->storeTemplateList($segment->get('customer')['store_id']);
			foreach ($template as $key => $value) {
				$tempModel = new StoreTemplate();
				$tempModel->load($value['id']);
				$tempModel->setData(['status'=>0]);
				$tempModel->save();
			}
			$model->setData(['status'=>1]);
			$model->save();			
			$result = ['status'=>TRUE];
		}
		
		echo json_encode($result);
		
	}
	
	public function funcAction(){
		$functions = $this->getRequest()->getQuery('functions');
		$part_id = $this->getRequest()->getQuery('part_id');		
		$root = $this->getLayout('decorationFunc_'.$functions);
		$root->getChild('main', true)->setVariable('data_tag', $functions);
		$root->getChild('main', true)->setVariable('part_id', $part_id);
		return $root;
	}
	
	public function getTemplateDataAction(){
		$dataParam = $this->getRequest()->getPost('dataParam');
		$dataTag = $this->getRequest()->getPost('dataTag');
	    $storeDecoration = new SDViewModel();
		$function_name = 'template_'.$dataTag;
		$view = $storeDecoration->$function_name($dataParam);
		echo json_encode(array('status'=>true,'view'=>$view));
	}
	
	public function decorationUploadAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $files = $this->getRequest()->getUploadedFile()['files'];
            $store = (new Segment('admin'))->get('user')->getStore();
            $result = $this->validateForm($data);
            if ($result['error'] === 0) {
                $categoryCollection = new Category();
				$categorys =  $categoryCollection->getCategoryByCode($data['resource_category_code']);
				$category = empty($categorys) ? null : $categorys[0]['id'];		
                try {
                    foreach ($files as $file) {
                        $name = $file->getClientFilename();
                        $model = new Model();
                        $model->moveFile($file)
                                ->setData([
                                    'store_id' => $store ? $store->getId() : (isset($data['store_id']) && $data['store_id'] ? $data['store_id'] : null),
                                    'uploaded_name' => $name,
                                    'file_type' => $file->getClientMediaType(),
                                    'category_id' => isset($data['category_id']) && $data['category_id'] ? $data['category_id'] : $category
                                ])->save();
                        $result['message'][] = ['message' => $this->translate('%s has been uploaded successfully.', [$name], 'resource'), 'level' => 'success'];
                    }
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate($e->getMessage()), 'level' => 'danger'];
                }
            }
        }
		
		$result['status'] = $result['error'];
        return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
    }
	

}
