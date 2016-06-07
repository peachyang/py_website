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
            $root->getChild('head')->setTitle('Edit User / User Management');
        } else {
            $root->getChild('head')->setTitle('Add New User / User Management');
        }
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Admin\\Model\\User', ':ADMIN/user/list/');
    }

    public function saveAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $segment = new Segment('admin');
            $user = $segment->get('user');
            $result = $this->validateForm($data, ['username', 'password']);
            if (empty($data['cpassword']) || empty($data['password']) || $data['cpassword'] !== $data['password']) {
                $result['message'][] = ['message' => $this->translate('The confirm password is not equal to the password.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else if (!$user->valid($user['username'], $data['crpassword'])) {
                $result['message'][] = ['message' => $this->translate('The current password is incurrect.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else if ($result['error'] === 0) {
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
            }
        }
        $referer = $this->getRequest()->getHeader('HTTP_REFERER');
        return $this->response($result, strpos($referer, 'edit') ? ':ADMIN/user/list/' : ':ADMIN/user/');
    }

}
