<?php

namespace Seahinet\Admin\Controller;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Admin\Model\Role as Model;

class RoleController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_role_list');
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_role_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
        }
        return $root;
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
                    $count = 0;
                    foreach ((array) $data['id'] as $id) {
                        $model->setId($id)->remove();
                        $count++;
                    }
                    $result['message'][] = ['message' => $this->translate('%d item(s) have been deleted successfully.', [$count]), 'level' => 'success'];
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
            return $this->redirect(':ADMIN/role/list/');
        }
    }

    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $segment = new Segment('admin');
            $user = $segment->get('user');
            $result = ['error' => 0, 'message' => []];
            if (!isset($data['csrf']) || !$this->validateCsrfKey($data['csrf'])) {
                $result['message'][] = ['message' => $this->translate('The form submitted did not originate from the expected site.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else if (empty($data['name'])) {
                $result['message'][] = ['message' => $this->translate('The name field is required and can not be empty.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else if ($user->valid($user['username'], $data['crpassword'])) {
                $model = new Model($data);
                if (!isset($data['id']) || (int) $data['id'] === 0) {
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
            } else {
                $result['message'][] = ['message' => $this->translate('The current password is incurrect.'), 'level' => 'danger'];
                $result['error'] = 1;
            }
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $result;
        } else {
            $this->addMessage($result['message'], 'danger', 'admin');
            return $this->redirect(':ADMIN/role/list/');
        }
    }

}
