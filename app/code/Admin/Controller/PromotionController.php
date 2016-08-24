<?php

namespace Seahinet\Admin\Controller;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Promotion\Model\Rule as Model;

class PromotionController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_promotion_list');
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_promotion_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Promotion Rule / Promotion');
        } else {
            $root->getChild('head')->setTitle('Add New Promotion Rule / Promotion');
        }
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Promotion\\Model\\Rule', ':ADMIN/promotion/');
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Promotion\\Model\\Rule', ':ADMIN/promotion/', ['name'], function($model, $data) {
                    $user = (new Segment('admin'))->get('user');
                    if ($user->getStore()) {
                        if ($model->getId() && $model->offsetGet('store_id') != $user->getStore()->getId()) {
                            throw new \Exception('Not allowed to save.');
                        }
                        $model->setData('store_id', $user->getStore()->getId());
                    } else if (!isset($data['store_id']) || (int) $data['store_id'] === 0) {
                        $model->setData('store_id', null);
                    }
                    if (!isset($data['from_date']) || strtotime($data['from_date']) > 0) {
                        $model->setData('from_date', null);
                    }
                    if (!isset($data['to_date']) || strtotime($data['from_date']) > 0) {
                        $model->setData('to_date', null);
                    }
                }
        );
    }

}
