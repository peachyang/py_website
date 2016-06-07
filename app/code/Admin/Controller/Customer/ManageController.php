<?php

namespace Seahinet\Admin\Controller\Customer;

use Exception;
use Seahinet\Customer\Model\Customer as Model;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Model\Eav\Type;
use Seahinet\Lib\Session\Segment;

class ManageController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_customer_list');
        return $root;
    }

    public function editAction()
    {
        $query = $this->getRequest()->getQuery();
        $root = $this->getLayout(!isset($query['id']) && !isset($query['attribute_set']) ? 'admin_customer_beforeedit' : 'admin_customer_edit');
        $model = new Model;
        if (isset($query['id'])) {
            $model->load($query['id']);
            $root->getChild('head')->setTitle('Edit Customer / Customer Management');
        } else {
            $root->getChild('head')->setTitle('Add New Customer / Customer Management');
        }
        $root->getChild('edit', true)->setVariable('model', $model);
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Customer\\Model\\Customer', ':ADMIN/customer_manage/');
    }

    public function saveAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $attributes = new Attribute;
            $attributes->withSet()->where(['is_required' => 1, 'attribute_set_id' => $data['attribute_set_id']])->columns(['code']);
            $required = ['store_id', 'language_id', 'attribute_set_id'];
            $attributes->walk(function ($attribute) use (&$required) {
                $required[] = $attribute['code'];
            });
            $result = $this->validateForm($data, $required);
            if ($result['error'] === 0) {
                $model = new Model($data['language_id'], $data);
                if (!isset($data['id']) || (int) $data['id'] === 0) {
                    $model->setId(null);
                }
                $type = new Type;
                $type->load(Model::ENTITY_TYPE, 'code');
                $model->setData('type_id', $type->getId());
                $user = (new Segment('admin'))->get('user');
                if ($user->getStore()) {
                    $model->setData('store_id', $user->getStore()->getId());
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
        return $this->response($result, ':ADMIN/customer_manage/');
    }

}
