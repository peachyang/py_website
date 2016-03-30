<?php

namespace Seahinet\Admin\Controller\CMS;

use Exception;
use Seahinet\CMS\Model\Page as Model;
use Seahinet\Lib\Controller\AuthActionController;

class PageController extends AuthActionController
{

    public function editAction()
    {
        
    }

    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (!isset($data['form_key']) || !$this->validateFormKey($data['form_key'])) {
                
            }
            $model = new Model($data);
            if (!isset($data['id'])) {
                $model->setId(null);
            }
            try {
                $model->save();
            } catch (Exception $e) {
                
            }
        }
        $this->redirectReferer();
    }

}
