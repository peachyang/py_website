<?php
namespace Seahinet\Admin\Controller\Resource;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;

use Seahinet\Resource\Model\Resource  as Model;

class ResourceController extends AuthActionController
{

    public function indexAction()
    {
    
        $root = $this->getLayout('admin_resource_list');
        return $root;
    
    }
    
    public function uploadImagesAction()
    {
        
        $result = ['error' => 0, 'message' => [],'files'=>[]];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $dataf = $this->getRequest()->getUploadedFile();
            $model = new Model();
            if ($result['error'] === 0) {
                foreach ($dataf as $keyf => $valf){

                    foreach ($valf as $index => $value){
                       $targetPath=$model->getTargetPath('images');
                       $fileName=$value->getClientFilename();
                       $value->moveTo($targetPath.$value->getClientFilename());
                       if($value->getMoved()){
                           $model->createImagesThumbnail($model->getTargetPath('images').$fileName,$targetPath,$fileName);
                       }
                       

                       $model->setData('store_id', 1);
                       $model->setData('file_name', $fileName);
                       $model->setData('old_name', $fileName);
                       $model->setData('file_type', 'images');
                       $model->setData('category_id', null);
                       
                       $model->save();
                    }
                    
                    
                }

                
            }
        }
        
        
        return $result;
    }

    public function popupListImagesAction()
    {
        $root = $this->getLayout('admin_popup_images_list');
        return $root;
    
    }
    

}
