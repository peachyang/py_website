<?php

namespace Seahinet\Admin\Controller\Api\Soap;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Api\Model\Soap\User as Model;

class UserController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_soap_user_list');
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_soap_user_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', TRUE)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit SOAP User / SOAP');
        } else {
            $root->getChild('messages', true)->addMessage($this->translate('Please make sure you have known the usage and risks of APIs.'), 'warning');
            $root->getChild('head')->setTitle('Add New SOAP User / SOAP');
        }
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Api\\Model\\Soap\\User', ':ADMIN/api_soap_user/');
    }

    public function saveAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $segment = new Segment('admin');
            $user = $segment->get('user');
            $result = $this->validateForm($data, ['role_id', 'username', 'password', 'cpassword', 'crpassword']);
            if (empty($data['cpassword']) || empty($data['cpassword']) || $data['password'] != $data['cpassword']) {
                $result['message'][] = ['message' => $this->translate('The confirm password is not equal to the password.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else if (!$user->valid($user['username'], $data['crpassword'])) {
                $result['message'][] = ['message' => $this->translate('The current password is incurrect.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else if ($result['error'] === 0) {
                try {
                    $model = new Model($data);
                    if (empty($data['id'])) {
                        $model->setId(null);
                    }
                    $model->save();
                    $result['message'][] = ['message' => $this->translate('An item has been saved successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while saving. Please check the log report or try again.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        return $this->response($result, ':ADMIN/api_soap_user/');
    }

}
