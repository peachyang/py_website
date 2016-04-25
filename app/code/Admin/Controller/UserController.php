<?php

namespace Seahinet\Admin\Controller;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Admin\Model\User as Model;

class UserController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_user');
        $segment = new Segment('admin');
        $root->getChild('edit', true)->setVariable('model', $segment->get('user'));
        return $root;
    }

    public function logoutAction()
    {
        $segment = new Segment('admin');
        $segment->set('isLoggedin', false);
        $segment->offsetUnset('user');
        return $this->redirect(':ADMIN');
    }

    public function listAction()
    {
        return $this->getLayout('admin_user_list');
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_user_edit');
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
            return $this->redirect(':ADMIN/user/list/');
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
            } else if (empty($data['username'])) {
                $result['message'][] = ['message' => $this->translate('The username field is required and can not be empty.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else if (empty($data['password'])) {
                $result['message'][] = ['message' => $this->translate('The password field is required and can not be empty.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else if (empty($data['cpassword']) || $data['cpassword'] !== $data['password']) {
                $result['message'][] = ['message' => $this->translate('The confirm password is not equal to the password.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else if ($user->valid($user['username'], $data['crpassword'])) {
                $model = new Model($data);
                if (!isset($data['id']) || (int) $data['id'] === 0) {
                    $model->setId(null);
                }
                try {
                    $model->save();
                    if (isset($data['id']) && $data['id'] == $user->getId()) {
                        $user->setData($data);
                        $segment->set('user', clone $user);
                    }
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
            $referer = $this->getRequest()->getHeader('HTTP_REFERER');
            if (strpos($referer, 'edit')) {
                return $this->redirect(':ADMIN/user/list/');
            }
            return $this->redirect(':ADMIN/user/');
        }
    }

}
