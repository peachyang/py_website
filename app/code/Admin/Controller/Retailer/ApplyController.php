<?php

namespace Seahinet\Admin\Controller\Retailer;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Model\Store;
use Seahinet\Lib\Session\Segment;
use Seahinet\Retailer\Model\{
    Application,
    Retailer
};

class ApplyController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_retailer_apply_list');
        return $root;
    }

    public function editAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            $root = $this->getLayout('admin_retailer_apply_edit');
            $model = new Application;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Application');
            return $root;
        }
        return $this->notFoundAction();
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Retailer\\Model\\Application', ':ADMIN/retailer_apply/', ['customer_id', 'status'], function($model, $data) {
                    $user = (new Segment('admin'))->get('user');
                    if ($user->getStore()) {
                        throw new \Exception('Not allowed to save.');
                    }
                    if ($data['status']) {
                        $store = new Store;
                        $code = 'retailer-' . $data['customer_id'];
                        $store->load($code, 'code');
                        if (!$store->getId()) {
                            $customer = new Customer;
                            $customer->load($data['customer_id']);
                            $store->setData([
                                'id' => null,
                                'merchant_id' => $customer->getStore()['merchant_id'],
                                'code' => $code,
                                'name' => $customer['username'] . '\'s Store',
                                'is_default' => 0,
                                'status' => 1
                            ])->save();
                            $retailer = new Retailer;
                            $retailer->setData([
                                'customer_id' => $data['customer_id'],
                                'store_id' => $store->getId(),
                                'name' => $customer['username'],
                                'uri_key' => $customer['username']
                            ])->save();
                        }
                    }
                }, true
        );
    }

}
