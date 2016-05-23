<?php

namespace Seahinet\Admin\Controller\I18n;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\I18n\Model\Translation as Model;

class TranslationController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_i18n_translation_list');
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_i18n_translation_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Translation');
        } else {
            $root->getChild('head')->setTitle('Add Translation');
        }
        return $root;
    }

    public function saveAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data);
            if ($result['error'] === 0) {
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
            }
        }
        return $this->response($result, ':ADMIN/i18n_translation/');
    }

    public function deleteAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isDelete()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data);
            if ($result['error'] === 0) {
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
        return $this->response($result, ':ADMIN/i18n_translation/');
    }

}
