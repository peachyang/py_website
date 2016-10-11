<?php

namespace Seahinet\Retailer\Controller;

use Exception;
use Seahinet\Retailer\Model\Retailer as Rmodel;
use Seahinet\Retailer\Model\StoreTemplate;
use Seahinet\Retailer\Model\StorePicinfo;
use Seahinet\Retailer\Model\Collection\StoreTemplateCollection;
use Seahinet\Retailer\Model\Collection\StorePicInfoCollection;
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
{   private $page_types = [
                '首页'=> 0,
                '产品详情页' => 1
                ];

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

    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $files = $this->getRequest()->getUploadedFile();
            $result = $this->validateForm($data, ['store', 'uri_key']);
            if ($result['error'] === 0) {
                try {
                    $segment = new Segment('customer');
                    $retailer = new Rmodel;
                    $retailer->load($segment->get('customer')->getId(), 'customer_id');
                    $store = $retailer->getStore();
                    if ($store['name'] !== $data['store']['name']) {
                        $store->setData('name', $data['store']['name'])->save();
                    }
                    unset($data['store_id'], $data['customer_id']);
                    $retailer->setData([
                        'profile' => isset($files['profile']) && !$files['profile']->getError() ? $files['profile']->getStream()->getContents() : null,
                        'watermark' => isset($files['watermark']) && !$files['watermark']->getError() ? $files['watermark']->getStream()->getContents() : null
                            ] + $data)->save();
                    $result['message'][] = ['message' => $this->translate('Store infomation has been updated successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected while saving. Please check the log report or try again.'), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result ?? ['error' => 1], 'retailer/store/setting/', 'retailer');
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

    public function viewSearchAction()
    {
        $root = $this->getLayout('view_search');
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
        $root->getChild('main', true)->setVariable('page_types', $this->page_types);
        return $root;
    }

    public function decorationListAction()
    {
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
        $r = new Rmodel;
        $r->load($segment->get('customer')->getId(), 'customer_id');
        $data['store_id'] = $r['store_id'];
        $model = new StoreTemplate();

        if ($data['template_id'] == '0' || $store_id == '0') {
            $model->setData($data);
            $model->save();
            $template_id = $model->getId();
        } else {
            $template_id = $data['template_id'];
            unset($data['template_id']);
            $model->load($template_id);
            $model->setData($data);
            $model->save();
        }

        $result = ['status' => true, 'id' => $template_id, 'store_id' => $data['store_id']];
        echo json_encode($result);
    }

    public function delTemplateAction()
    {
        $data = $this->getRequest()->getPost();
        $segment = new Segment('customer');
        $r = new Rmodel;
        $r->load($segment->get('customer')->getId(), 'customer_id');
        $store_id = $r['store_id'];

        $model = new StoreTemplate();
        $model->load($data['id']);
        if ($model['store_id'] != $store_id)
            $result = ['status' => FALSE];
        else {
            $model->remove();
            $result = ['status' => TRUE];
        }

        echo json_encode($result);
    }

    public function setTemplateAction()
    {
        $data = $this->getRequest()->getPost();
        $segment = new Segment('customer');
        $r = new Rmodel;
        $r->load($segment->get('customer')->getId(), 'customer_id');
        $store_id = $r['store_id'];

        $model = new StoreTemplate();
        $model->load($data['id']);
        if ($model['store_id'] != $store_id)
            $result = ['status' => FALSE];
        else {
            $template = new StoreTemplateCollection();
            $template->storeTemplateList($store_id);
            foreach ($template as $key => $value) {
                $tempModel = new StoreTemplate();
                $tempModel->load($value['id']);
                $tempModel->setData(['status' => 0]);
                $tempModel->save();
            }
            $model->setData(['status' => 1]);
            $model->save();
            $result = ['status' => TRUE];
        }

        echo json_encode($result);
    }

    public function funcAction()
    {
        $functions = $this->getRequest()->getQuery('functions');
        $part_id = $this->getRequest()->getQuery('part_id');
        $root = $this->getLayout('decorationFunc_' . $functions);
        $root->getChild('main', true)->setVariable('data_tag', $functions);
        $root->getChild('main', true)->setVariable('part_id', $part_id);
        return $root;
    }

    public function getTemplateDataAction()
    {
        $dataParam = $this->getRequest()->getPost('dataParam');
        $dataTag = $this->getRequest()->getPost('dataTag');
        $storeDecoration = new SDViewModel();
        $function_name = 'template_' . $dataTag;
        $view = $storeDecoration->$function_name($dataParam);
        echo json_encode(array('status' => true, 'view' => $view));
    }

    public function decorationInfoAddAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $segment = new Segment('customer');
            $data = $this->getRequest()->getPost();
            $r = new Rmodel;
            $r->load($segment->get('customer')->getId(), 'customer_id');
            $store = $r['store_id'];
            $storePicinfo = new StorePicinfo();
            try {
                $storePicinfo->setData([
                    'store_id' => $store,
                    'pic_title' => $data['title'],
                    'url' => $data['url'],
                    'resource_category_code' => $data['resource_category_code'],
                    'resource_id' => null,
                    'sort_order' => 0
                ]);
                $storePicinfo->save();
            } catch (Exception $e) {
                $result['error'] = 1;
            }
            $storePicinfo->setData(['sort_order' => $storePicinfo->getId()]);
            $storePicinfo->save();
        }
        $storeDecoration = new SDViewModel();
        $result['status'] = $result['error'];
        $result['Info'] = $storeDecoration->getStorePicInfo($data['resource_category_code']);
        return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
    }

    public function decorationInfoDeleteAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $segment = new Segment('customer');
            $r = new Rmodel;
            $r->load($segment->get('customer')->getId(), 'customer_id');
            $data = $this->getRequest()->getPost();
            $storePicinfo = new StorePicinfo;
            $storePicinfo->load($data['id']);
            if ($storePicinfo->getId() && $storePicinfo['store_id'] == $r['store_id'])
                $storePicinfo->remove();
            else
                $result['error'] = 1;
        }
        $storeDecoration = new SDViewModel();
        $result['status'] = $result['error'];
        $result['Info'] = $storeDecoration->getStorePicInfo($data['resource_category_code']);
        return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
    }

    public function decorationUploadAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $segment = new Segment('customer');
            $data = $this->getRequest()->getPost();
            $files = $this->getRequest()->getUploadedFile()['files'];
            $r = new Rmodel;
            $r->load($segment->get('customer')->getId(), 'customer_id');
            $store = $r['store_id'];
            $result = $this->validateForm($data);
            if ($result['error'] === 0) {
                $categoryCollection = new Category();
                $categorys = $categoryCollection->getCategoryByCode($data['resource_category_code']);
                $category = empty($categorys) ? null : $categorys[0]['id'];
                try {
                    foreach ($files as $file) {
                        $name = $file->getClientFilename();
                        $model = new Model();
                        $model->moveFile($file)
                                ->setData([
                                    'store_id' => $store,
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
            if ($result['error'] === 0) {
                $storePicinfo = new StorePicinfo();
                $storePicinfo->setData([
                    'store_id' => $store,
                    'pic_title' => $data['pic_title'],
                    'url' => $data['url'],
                    'resource_category_code' => $data['resource_category_code'],
                    'resource_id' => $model->getId(),
                    'sort_order' => $model->getId()
                ]);
                $storePicinfo->save();
            }
        }

        $storeDecoration = new SDViewModel();
        $result['picInfo'] = $storeDecoration->getStorePicInfo($data['resource_category_code']);
        $result['status'] = $result['error'];
        return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
    }

    public function decorationUploadDeleteAction()
    {
        $result = ['error' => 0, 'message' => []];
        $segment = new Segment('customer');
        $data = $this->getRequest()->getPost();
        if ($result['error'] === 0) {
            try {
                $path = BP . Model::$options['path'];

                $model = new Model;
                $model->load($data['resource_id']);
                if ($model->getId()) {
                    $type = $model['file_type'];
                    $r = new Rmodel;
                    $r->load($segment->get('customer')->getId(), 'customer_id');
                    if ($model['store_id'] == $r['store_id']) {
                        $model->remove();
                    }

                    $storePicinfo = new StorePicinfo;
                    $storePicinfo->load($data['id']);
                    if ($storePicinfo->getId() && $storePicinfo['store_id'] == $r['store_id'])
                        $storePicinfo->remove();
                }
            } catch (Exception $e) {
                $this->getContainer()->get('log')->logException($e);

                $result['error'] = 1;
            }
        }
        $storeDecoration = new SDViewModel();
        $result['status'] = $result['error'];
        $result['picInfo'] = $storeDecoration->getStorePicInfo($data['resource_category_code']);
        return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
    }

    public function decorationUploadSaveAction()
    {
        $result = ['error' => 0, 'message' => []];
        $segment = new Segment('customer');
        $data = $this->getRequest()->getPost();
        if ($result['error'] === 0) {
            try {
                $storePicinfo = new StorePicinfo;
                $storePicinfo->load($data['id']);
                $r = new Rmodel;
                $r->load($segment->get('customer')->getId(), 'customer_id');
                if ($storePicinfo->getId() && $storePicinfo['store_id'] == $r['store_id']) {
                    $storePicinfo->setData([
                        'pic_title' => $data['pic_title'],
                        'url' => $data['url']
                    ]);
                    $storePicinfo->save();
                }
            } catch (Exception $e) {
                $this->getContainer()->get('log')->logException($e);

                $result['error'] = 1;
            }
        }

        $result['status'] = $result['error'];
        return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
    }

    public function decorationUploadForBannerAction()
    {
        $this->decorationDeleteForBanner();
        $result = ['error' => 0, 'message' => []];
        $name = '';
        if ($this->getRequest()->isPost()) {
            $segment = new Segment('customer');
            $data = $this->getRequest()->getPost();
            $files = $this->getRequest()->getUploadedFile()['files'];
            $r = new Rmodel;
            $r->load($segment->get('customer')->getId(), 'customer_id');
            $store = $r['store_id'];
            if ($result['error'] === 0) {
                try {
                    foreach ($files as $file) {
                        $name = $file->getClientFilename();
                        $model = new Model();
                        $model->moveFile($file)
                                ->setData([
                                    'store_id' => $store,
                                    'uploaded_name' => $name,
                                    'file_type' => $file->getClientMediaType(),
                                    'category_id' => null
                                ])->save();
                        $name = $model['real_name'];
                        $result['message'][] = ['message' => $this->translate('%s has been uploaded successfully.', [$name], 'resource'), 'level' => 'success'];
                    }
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate($e->getMessage()), 'level' => 'danger'];
                }
            }
            if ($result['error'] === 0) {
                $Rmodel = new Rmodel;
                $Rmodel->load($data['retailer_id']);
                if ($Rmodel['store_id'] == $store) {
                    $Rmodel->setData(['banner' => $model->getId()]);
                    $Rmodel->save();
                }
            }
        }

        $result['picInfo'] = $name;
        $result['status'] = $result['error'];
        return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
    }

    public function decorationUploadDeleteForBannerAction()
    {
        $result = $this->decorationDeleteForBanner();
        return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
    }

    public function decorationDeleteForBanner()
    {

        $result = ['error' => 0, 'message' => []];
        $segment = new Segment('customer');
        $data = $this->getRequest()->getPost();
        if ($result['error'] === 0) {
            $storeDecoration = new SDViewModel();
            $retailer = $storeDecoration->getStoreBanner();
            if (!empty($retailer['banner'])) {
                try {
                    $path = BP . Model::$options['path'];

                    $model = new Model;
                    $model->load($retailer['banner']);
                    if ($model->getId()) {
                        $type = $model['file_type'];
                        $r = new Rmodel;
                        $r->load($segment->get('customer')->getId(), 'customer_id');
                        if ($model['store_id'] == $r['store_id']) {
                            $model->remove();
                        }
                    }
                    $Rmodel = new Rmodel;
                    $Rmodel->load($retailer['id']);
                    $Rmodel->setData(['banner' => null]);
                    $Rmodel->save();
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);

                    $result['error'] = 1;
                }
            }
        }
        $result['status'] = $result['error'];
        return $result;
    }

}
