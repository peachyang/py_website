<?php
namespace Seahinet\Admin\Controller\Resources;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;

use Seahinet\Resources\Model\Resources  as Model;

class ResourcesController extends AuthActionController
{

    public function indexAction()
    {
    
        $root = $this->getLayout('admin_resources_list');
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
                       $targetPath=$model->getTargetPath(1);
                       $fileName=$value->getClientFilename();
                       $value->moveTo($targetPath.$value->getClientFilename());
                       if($value->getMoved()){
                           $model->createImagesThumbnail($model->getTargetPath(1).$fileName,$targetPath,$fileName);
                       }
                       

                       $model->setData('store_id', 0);
                       $model->setData('file_name', $fileName);
                       $model->setData('old_name', $fileName);
                       $model->setData('file_type', 1);
                       $model->setData('category_id', 0);
                       
                       $model->save();
                    }
                    
                    
                }

                
            }
        }
        
        
        return $result;
    }


}
