<?php

namespace Seahinet\Admin\Controller\CMS;

use Exception;
use Seahinet\CMS\Model\Page as Model;
use Seahinet\CMS\Model\Collection\Page as Collection;
use Seahinet\Lib\Controller\AuthActionController;

class PageController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_cms_page_list');
        return $root;
    }

    public function editAction()
    {
        
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isDelete()) {
            $data = $this->getRequest()->getPost();
            $result = ['error' => 0, 'message' => []];
            if (!isset($data['csrf']) || !$this->validateCsrfKey($data['csrf'])) {
                $result['message'][] = ['message' => $this->translate('The form submitted did not originate from the expected site.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else {
                try {
                    $model = new Model;
                    $model->setId($data['id'])->remove();
                    $result['message'][] = ['message' => $this->translate('An item has been deleted successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while deleting. Please check the log report or try again.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $result;
        } else {
            $this->addMessage($result['message'], 'danger', 'admin');
            return $this->redirect(':ADMIN/cms_page/');
        }
    }

    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = ['error' => 0, 'message' => []];
            if (!isset($data['csrf']) || !$this->validateCsrfKey($data['csrf'])) {
                $result['message'][] = ['message' => $this->translate('The form submitted did not originate from the expected site.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else {
                $model = new Model($data);
                if (!isset($data['id'])) {
                    $model->setId(null);
                }
                try {
                    $model->save();
                    $result['message'][] = ['message' => $this->translate('An item has been saved successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while saving. Please check the log report or try again.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $result;
        } else {
            $this->addMessage($result['message'], 'danger', 'admin');
            return $this->redirectReferer();
        }
    }

}
