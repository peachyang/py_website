<?php

namespace Seahinet\Admin\Controller\Api\Soap;

use Seahinet\Api\Model\Soap\Role as Model;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;

class RoleController extends AuthActionController
{
   public function indexAction() 
   {
       $root = $this->getLayout('admin_soap_role_list');
       return $root;
   }
   public function editAction()
    {
        $root = $this->getLayout('admin_soap_role_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit SOAP Role / SOAP Role Management');
        } else {
            $root->getChild('head')->setTitle('Add New SOAP Role / SOAP Role Management');
        }
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Api\\Model\\Soap\\Role', ':ADMIN/api_soap_role/list/');
    }
    public function saveAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $segment = new Segment('admin');
            $user = $segment->get('user');
            $result = $this->validateForm($data, ['name']);
            if (!$user->valid($user['username'], $data['crpassword'])) {
                $result['message'][] = ['message' => $this->translate('The current password is incurrect.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else {
                $model = new Model($data);
                if (empty($data['id'])) {
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
        return $this->response($result, ':ADMIN/api_soap_role/');
    }
}
