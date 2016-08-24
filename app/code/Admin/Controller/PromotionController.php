<?php

namespace Seahinet\Admin\Controller;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Promotion\Model\Coupon;
use Seahinet\Promotion\Model\Rule as Model;
use Zend\Math\Rand;

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

    public function deleteCouponAction()
    {
        return $this->doDelete('\\Seahinet\\Promotion\\Model\\Coupon');
    }

    public function generateCouponAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $data = $this->getRequest()->getPost();
            if (!isset($data['coupon'])) {
                $data['coupon'] = ['code' => []];
            } else if (!isset($data['count'])) {
                $data['count'] = 0;
            }
            $result = [];
            for ($i = 0; $i < $data['count'];) {
                $code = Rand::getString(10, 'abcdefghijkmnopqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWXYZ');
                if (!in_array($code, $result) && !in_array($code, $data['coupon']['code']) && !(new Coupon)->load($code, 'code')->getId()) {
                    $result[] = $code;
                    $i++;
                }
            }
            return $result;
        }
        return $this->notFoundAction();
    }

}
